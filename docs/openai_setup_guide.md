# OPENAI SETUP GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides step-by-step instructions for setting up OpenAI API integration for the Tour Guide Application to enable AI-powered features like smart recommendations, natural language processing, and advanced chat capabilities.

## OPENAI ACCOUNT SETUP

### 1. Create OpenAI Account
1. Go to [OpenAI](https://openai.com)
2. Click "Sign Up"
3. Enter your email address and password
4. Verify your email address
5. Complete phone verification

### 2. Get API Key
1. Log in to OpenAI dashboard
2. Navigate to API Keys section
3. Click "Create new secret key"
4. Name your key (e.g., "MyWisata Production")
5. Copy the API key (starts with `sk-`)
6. Store it securely - you won't see it again

## ENVIRONMENT CONFIGURATION

### Update .env File
```env
OPENAI_API_KEY=sk-your-actual-api-key-here
OPENAI_MODEL=gpt-4
OPENAI_TEMPERATURE=0.7
OPENAI_MAX_TOKENS=1000
OPENAI_ORGANIZATION=
```

### Configuration Options
- **OPENAI_API_KEY** - Your OpenAI API key (required)
- **OPENAI_MODEL** - Model to use (gpt-4, gpt-3.5-turbo)
- **OPENAI_TEMPERATURE** - Creativity level (0.0-1.0, default 0.7)
- **OPENAI_MAX_TOKENS** - Maximum response tokens (default 1000)
- **OPENAI_ORGANIZATION** - Organization ID (optional)

## MODEL SELECTION

### Available Models
- **gpt-4** - Most capable, higher cost
- **gpt-3.5-turbo** - Fast, cost-effective
- **gpt-4-turbo** - Latest GPT-4 model

### Recommendations
- Use `gpt-3.5-turbo` for development and testing
- Use `gpt-4` for production when quality is critical
- Consider `gpt-4-turbo` for best performance/cost balance

## COST MANAGEMENT

### Pricing (as of 2026)
- GPT-3.5 Turbo: ~$0.002 per 1K tokens
- GPT-4: ~$0.03 per 1K tokens (input), ~$0.06 per 1K tokens (output)

### Cost Optimization Tips
1. Use appropriate model for each task
2. Set reasonable max_tokens limits
3. Cache AI responses when possible
4. Implement rate limiting
5. Monitor usage regularly
6. Use streaming for long responses

### Usage Monitoring
Check OpenAI dashboard for:
- Daily token usage
- Cost breakdown
- API request counts
- Error rates

## SECURITY BEST PRACTICES

### API Key Security
- Never commit API keys to version control
- Use environment variables
- Rotate keys regularly
- Revoke unused keys
- Use separate keys for dev/prod

### Data Privacy
- Don't send sensitive user data
- Anonymize data before sending
- Review OpenAI's data policy
- Implement data retention policies
- Comply with GDPR/privacy laws

## TESTING

### Test API Connection
```php
$openai = new \App\Services\OpenAIService();
$response = $openai->chat([
    ['role' => 'user', 'content' => 'Hello, can you hear me?']
]);

if ($response['success']) {
    echo "API connection successful!";
} else {
    echo "API connection failed: " . $response['error'];
}
```

### Test Features
1. Test destination recommendations
2. Test tour guide recommendations
3. Test itinerary generation
4. Test content generation
5. Test sentiment analysis
6. Test chat conversation
7. Test translation

## RATE LIMITING

### OpenAI Rate Limits
- Free tier: 3 requests per minute
- Pay-as-you-go: 60 requests per minute
- Custom limits available for enterprise

### Implement Rate Limiting
```php
// Check user's daily usage before making request
$usageTracker = new \App\Services\UsageTracker();
if (!$usageTracker->checkLimit($userId, $maxTokens)) {
    return ['success' => false, 'error' => 'Daily limit exceeded'];
}
```

## ERROR HANDLING

### Common Errors
- **Invalid API Key** - Check your API key
- **Rate Limit Exceeded** - Implement backoff strategy
- **Insufficient Quota** - Check your billing
- **Model Not Found** - Verify model name
- **Timeout** - Increase timeout or reduce complexity

### Fallback Strategy
When OpenAI API fails:
1. Return cached response if available
2. Provide default recommendations
3. Show user-friendly error message
4. Log error for debugging

## PRODUCTION CHECKLIST

- [ ] API key configured in environment
- [ ] Appropriate model selected
- [ ] Rate limiting implemented
- [ ] Usage tracking enabled
- [ ] Error handling robust
- [ ] Cost monitoring active
- [ ] Data privacy reviewed
- [ ] Backup plan in place
- [ ] Testing completed
- [ ] Documentation updated

## TROUBLESHOOTING

### API Key Not Working
- Verify key is correct
- Check key has proper permissions
- Ensure billing is set up
- Check OpenAI service status

### High Costs
- Review usage patterns
- Implement caching
- Reduce max_tokens
- Switch to cheaper model
- Add rate limiting

### Slow Responses
- Use gpt-3.5-turbo instead of gpt-4
- Reduce max_tokens
- Implement streaming
- Use caching
- Optimize prompts

## RESOURCES

- [OpenAI Documentation](https://platform.openai.com/docs)
- [OpenAI API Reference](https://platform.openai.com/docs/api-reference)
- [OpenAI Pricing](https://openai.com/pricing)
- [OpenAI Status](https://status.openai.com/)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18
