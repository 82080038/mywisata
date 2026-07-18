<?php

/**
 * MyWisata Application - Translation Controller
 *
 * Real-time speech-to-speech translation API.
 * Uses Web Speech API (browser) for STT/TTS + LibreTranslate/Google for translation.
 * Caches translations to reduce API calls.
 */

require_once APP_ROOT . '/app/models/Facility.php';

class TranslationController extends Controller
{
    private $supportedLanguages = [
        'id' => 'Bahasa Indonesia',
        'en' => 'English',
        'ja' => '日本語',
        'zh' => '普通话',
        'ko' => '한국어',
        'ar' => 'العربية',
        'es' => 'Español',
        'fr' => 'Français',
        'nl' => 'Nederlands',
        'de' => 'Deutsch',
    ];

    /**
     * Get supported languages
     */
    public function languages()
    {
        $db = Database::getInstance();
        $langs = $db->query("SELECT code, name, native_name FROM languages ORDER BY id")->fetchAll();
        $this->json(['status' => 'success', 'languages' => $langs]);
    }

    /**
     * Translate text (AJAX endpoint)
     * POST: source_lang, target_lang, text
     */
    public function translate()
    {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }

        $sourceLang = $this->post('source_lang', 'id');
        $targetLang = $this->post('target_lang', 'en');
        $text = trim($this->post('text', ''));

        if (empty($text)) {
            $this->json(['status' => 'error', 'message' => 'Text is required'], 400);
        }

        if ($sourceLang === $targetLang) {
            $this->json([
                'status' => 'success',
                'translated_text' => $text,
                'cached' => true,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
            ]);
        }

        // Check cache first
        $cached = $this->getCachedTranslation($sourceLang, $targetLang, $text);
        if ($cached) {
            $this->json([
                'status' => 'success',
                'translated_text' => $cached,
                'cached' => true,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
            ]);
        }

        // Call translation API
        $translated = $this->callTranslationAPI($sourceLang, $targetLang, $text);

        if ($translated === null) {
            // Fallback: return original text with note
            $this->json([
                'status' => 'success',
                'translated_text' => $text,
                'cached' => false,
                'fallback' => true,
                'message' => 'Translation service unavailable, returning original text',
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
            ]);
        }

        // Cache the translation
        $this->cacheTranslation($sourceLang, $targetLang, $text, $translated);

        $this->json([
            'status' => 'success',
            'translated_text' => $translated,
            'cached' => false,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
        ]);
    }

    /**
     * Batch translate multiple texts
     * POST: source_lang, target_lang, texts (JSON array)
     */
    public function translateBatch()
    {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }

        $sourceLang = $this->post('source_lang', 'id');
        $targetLang = $this->post('target_lang', 'en');
        $texts = json_decode($this->post('texts', '[]'), true);

        if (empty($texts) || !is_array($texts)) {
            $this->json(['status' => 'error', 'message' => 'Texts array required'], 400);
        }

        $results = [];
        foreach ($texts as $text) {
            if (empty(trim($text))) {
                $results[$text] = $text;
                continue;
            }

            if ($sourceLang === $targetLang) {
                $results[$text] = $text;
                continue;
            }

            $cached = $this->getCachedTranslation($sourceLang, $targetLang, $text);
            if ($cached) {
                $results[$text] = $cached;
                continue;
            }

            $translated = $this->callTranslationAPI($sourceLang, $targetLang, $text);
            if ($translated !== null) {
                $this->cacheTranslation($sourceLang, $targetLang, $text, $translated);
                $results[$text] = $translated;
            } else {
                $results[$text] = $text;
            }
        }

        $this->json([
            'status' => 'success',
            'results' => $results,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
        ]);
    }

    /**
     * Get cached translation from database
     */
    private function getCachedTranslation($sourceLang, $targetLang, $text)
    {
        $db = Database::getInstance();
        $hash = hash('sha256', $text);
        $sql = "SELECT translated_text FROM translation_cache 
                WHERE source_lang = :sl AND target_lang = :tl AND source_hash = :hash";
        $result = $db->query($sql, ['sl' => $sourceLang, 'tl' => $targetLang, 'hash' => $hash])->fetch();
        if ($result) {
            // Increment hit count
            $db->query(
                "UPDATE translation_cache SET hit_count = hit_count + 1 WHERE source_lang = ? AND target_lang = ? AND source_hash = ?",
                [$sourceLang, $targetLang, $hash]
            );
            return $result['translated_text'];
        }
        return null;
    }

    /**
     * Cache translation in database
     */
    private function cacheTranslation($sourceLang, $targetLang, $sourceText, $translatedText)
    {
        $db = Database::getInstance();
        $hash = hash('sha256', $sourceText);
        $sql = "INSERT INTO translation_cache (source_lang, target_lang, source_text, source_hash, translated_text, provider)
                VALUES (:sl, :tl, :st, :sh, :tt, 'libretranslate')
                ON DUPLICATE KEY UPDATE translated_text = :tt2, hit_count = hit_count + 1";
        $db->query($sql, [
            'sl' => $sourceLang,
            'tl' => $targetLang,
            'st' => $sourceText,
            'sh' => $hash,
            'tt' => $translatedText,
            'tt2' => $translatedText,
        ]);
    }

    /**
     * Call external translation API
     * Tries LibreTranslate first, falls back to Google Translate unofficial endpoint
     */
    private function callTranslationAPI($sourceLang, $targetLang, $text)
    {
        // Method 1: LibreTranslate (self-hosted or public instance)
        $result = $this->callLibreTranslate($sourceLang, $targetLang, $text);
        if ($result !== null) {
            return $result;
        }

        // Method 2: Google Translate unofficial endpoint (free, no API key)
        $result = $this->callGoogleTranslate($sourceLang, $targetLang, $text);
        if ($result !== null) {
            return $result;
        }

        // Method 3: MyMemory Translation API (free, no key)
        $result = $this->callMyMemory($sourceLang, $targetLang, $text);
        if ($result !== null) {
            return $result;
        }

        return null;
    }

    /**
     * LibreTranslate API
     */
    private function callLibreTranslate($source, $target, $text)
    {
        $url = 'https://libretranslate.com/translate';
        $data = json_encode([
            'q' => $text,
            'source' => $source,
            'target' => $target,
            'format' => 'text',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['translatedText'])) {
                return $data['translatedText'];
            }
        }
        return null;
    }

    /**
     * Google Translate unofficial endpoint (free)
     */
    private function callGoogleTranslate($source, $target, $text)
    {
        $url = 'https://translate.googleapis.com/translate_a/single?client=gtx'
            . '&sl=' . urlencode($source)
            . '&tl=' . urlencode($target)
            . '&dt=t&q=' . urlencode($text);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (MyWisata Translation Module)',
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data[0]) && is_array($data[0])) {
                $translated = '';
                foreach ($data[0] as $segment) {
                    if (isset($segment[0])) {
                        $translated .= $segment[0];
                    }
                }
                return trim($translated);
            }
        }
        return null;
    }

    /**
     * MyMemory Translation API (free, 5000 words/day)
     */
    private function callMyMemory($source, $target, $text)
    {
        $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($text)
            . '&langpair=' . urlencode($source . '|' . $target);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['responseData']['translatedText'])) {
                return $data['responseData']['translatedText'];
            }
        }
        return null;
    }

    /**
     * Get translation widget page (standalone)
     */
    public function widget()
    {
        $db = Database::getInstance();
        $languages = $db->query("SELECT code, name, native_name FROM languages ORDER BY id")->fetchAll();

        $data = [
            'title' => 'Real-Time Translator - MyWisata',
            'languages' => $languages,
            'csrf_token' => Middleware::csrfToken(),
        ];
        $this->view('translation/widget', $data);
    }

    /**
     * Get cache statistics (admin)
     */
    public function stats()
    {
        $db = Database::getInstance();
        $totalCache = $db->query("SELECT COUNT(*) as cnt FROM translation_cache")->fetch();
        $totalHits = $db->query("SELECT SUM(hit_count) as cnt FROM translation_cache")->fetch();
        $byLangPair = $db->query(
            "SELECT source_lang, target_lang, COUNT(*) as cnt, SUM(hit_count) as hits
             FROM translation_cache GROUP BY source_lang, target_lang ORDER BY hits DESC LIMIT 10"
        )->fetchAll();

        $this->json([
            'status' => 'success',
            'total_cached' => $totalCache['cnt'],
            'total_hits' => $totalHits['cnt'] ?? 0,
            'by_lang_pair' => $byLangPair,
        ]);
    }
}
