# Filtah

A WordPress plugin that uses AI to automatically reply to your blog comments with intelligent, contextual responses.

## Description

Filtah integrates with AI providers (OpenAI or Groq) to generate thoughtful replies to comments on your WordPress blog. The plugin helps blog owners maintain engagement by providing automated, contextual responses to visitor comments.

## Features

- **Multi-AI Provider Support**: Choose between OpenAI (ChatGPT) and Groq AI
- **Multiple Model Options**: Support for various AI models including GPT-3.5, GPT-4, Llama 3.3, and more
- **Automatic Comment Replies**: Generates intelligent responses to new comments automatically
- **Bulk Reply Generation**: Generate replies for all existing comments with one click
- **Smart Content Processing**: Automatically trims long content to optimize API costs
- **Rate Limiting**: Built-in protection against API abuse
- **Error Logging**: Comprehensive logging for debugging and monitoring
- **Multilingual Support**: Ready for translation with included language files
- **Clean Admin Interface**: User-friendly settings panel with dynamic options

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- An API key from either OpenAI or Groq

## Privacy and Data Usage

**Important**: This plugin sends your blog post content and visitor comments to external AI services for processing. Please note:

- Content is sent to OpenAI or Groq APIs based on your provider selection
- No data is permanently stored by this plugin beyond standard WordPress comment metadata
- All content processing happens in real-time
- Please review the privacy policies of your chosen AI provider:
  - [OpenAI Privacy Policy](https://openai.com/policies/privacy-policy)
  - [Groq Privacy Policy](https://groq.com/privacy-policy)

By using this plugin, you acknowledge that blog content and comments will be processed by external AI services.

## Installation

1. Download the plugin files
2. Upload the `filtah` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your AI provider and API key in Settings → Filtah

## Configuration

### 1. AI Provider Setup

Navigate to **Settings → Filtah** in your WordPress admin panel:

1. Choose AI Provider: Select between OpenAI or Groq
2. Enter API Key: 
   - For OpenAI: Get your key from [OpenAI Platform](https://platform.openai.com/api-keys)
   - For Groq: Get your key from [Groq Console](https://console.groq.com/keys)
3. Select Model: Choose from available models based on your provider
4. Set Default Status: Choose whether AI replies should be approved or require moderation

### 2. Available Models

#### OpenAI Models:
- GPT-3.5 Turbo (Default)
- GPT-4
- GPT-4 Turbo

#### Groq Models:
- Llama 3.3 70B (Default)
- Llama 3.1 70B
- Mixtral 8x7B

## Usage

### Automatic Replies
Once configured, Filtah will automatically generate replies to new comments on your blog posts.

### Manual Bulk Processing
Use the "Generate" button in the settings to create replies for all existing comments that haven't been replied to yet.

### Comment Management
- AI-generated replies are clearly marked in your WordPress admin
- You can edit, delete, or moderate AI replies just like regular comments
- The plugin tracks which comments have been replied to prevent duplicates

## Advanced Features

### Rate Limiting
Filtah includes built-in rate limiting (2 seconds between requests) to:
- Prevent API abuse
- Respect provider limits
- Avoid overwhelming your server

### Content Optimization
- Blog posts are trimmed to 2000 characters
- Comments are trimmed to 500 characters
- This helps control API costs while maintaining context

### Error Handling
Comprehensive error logging when `WP_DEBUG` is enabled:
- API errors
- Network issues
- Rate limiting events
- Invalid responses

## Internationalization

Filtah is ready for translation and includes:
- POT file for translators
- Text domain: `filtah`

To translate:
1. Use the included `filtah.pot` file
2. Create translations in the `languages/` directory
3. Follow WordPress i18n standards

## Troubleshooting

### Common Issues

**AI replies not generating:**
- Check your API key is valid
- Ensure you have sufficient API credits
- Verify rate limiting isn't blocking requests
- Check WordPress error logs

**Bulk generation stops:**
- Large sites may hit rate limits
- Check your hosting provider's timeout settings
- Process comments in smaller batches

**Formatting issues:**
- Groq responses are automatically parsed from markdown
- OpenAI uses direct JSON formatting
- Both are handled transparently

### Debug Mode

Enable WordPress debug mode to see detailed error logs:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check `/wp-content/debug.log` for Filtah-specific errors.

## 📊 API Usage & Costs

### OpenAI Pricing (Approximate)
- GPT-3.5 Turbo: ~$0.002 per 1K tokens
- GPT-4: ~$0.03 per 1K tokens

### Groq Pricing
- Generally more cost-effective than OpenAI
- Check current rates at [Groq Pricing](https://groq.com/pricing)

### Cost Optimization Tips
- Use content trimming (enabled by default)
- Monitor your API usage regularly
- Consider using rate limiting during high-traffic periods

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Development Setup

```bash
# Clone the repository
git clone https://github.com/moustafa-brahimi/filtah.git

# Install in WordPress
cp -r filtah /path/to/wordpress/wp-content/plugins/

# Enable WordPress debug mode for development
```

## 📝 Changelog

### Version 1.0.0
- Initial release
- Multi-provider support (OpenAI/Groq)
- Multiple model options
- Automatic comment replies
- Bulk processing
- Rate limiting
- Error logging
- Multilingual support

## 🔒 Security

- All API keys are stored securely in WordPress options
- Nonce verification for all AJAX requests
- Input sanitization and validation
- Rate limiting protection
- Error logging without exposing sensitive data

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 👨‍💻 Author

**BRAHIMI Moustafa**

## 📞 Support

- **Documentation**: Check this README
- **Issues**: Report bugs via GitHub Issues
- **Feature Requests**: Submit via GitHub Issues

## 🙏 Acknowledgments

- OpenAI for their powerful GPT models
- Groq for fast, efficient AI processing
- WordPress community for the amazing platform
- Contributors and beta testers

---

**Made with ❤️ for the WordPress community**

*Filtah - Making blog engagement smarter, one comment at a time.*
