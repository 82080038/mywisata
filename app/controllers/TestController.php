<?php
/**
 * MyWisata Application - Test Controller
 * 
 * Handles test pages for development and testing purposes.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-17
 */

class TestController extends Controller {
    
    /**
     * Address cascade test page
     */
    public function address() {
        $data = [
            'title' => 'Address Cascade Test - MyWisata'
        ];
        $this->view('test/address', $data);
    }

    /**
     * Voice input test page
     */
    public function voiceInput() {
        $data = [
            'title' => 'Voice Input Test - MyWisata'
        ];
        $this->view('test/voice-input', $data);
    }
}
