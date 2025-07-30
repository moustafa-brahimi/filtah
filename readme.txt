=== Filtah ===
Plugin Name:         Filtah
Contributors: usuual
Tags: comments, ai, automation, replies
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically reply to blog comments using AI providers like OpenAI or Groq for intelligent, contextual responses.

== Description ==

Filtah integrates with AI providers (OpenAI or Groq) to generate thoughtful replies to comments on your WordPress blog. The plugin helps blog owners maintain engagement by providing automated, contextual responses to visitor comments.

= Key Features =

* Multi-AI Provider Support: Choose between OpenAI (ChatGPT) and Groq AI
* Multiple Model Options: Support for various AI models including GPT-3.5, GPT-4, Llama 3.3, and more
* Automatic Comment Replies: Generates intelligent responses to new comments automatically
* Bulk Reply Generation: Generate replies for all existing comments with one click
* Smart Content Processing: Automatically trims long content to optimize API costs
* Rate Limiting: Built-in protection against API abuse
* Error Logging: Comprehensive logging for debugging and monitoring
* Multilingual Support: Ready for translation with included language files

= Privacy Notice =

This plugin sends your blog post content and visitor comments to external AI services for processing. Please note:

* Content is sent to OpenAI or Groq APIs based on your provider selection
* No data is permanently stored by this plugin beyond standard WordPress comment metadata
* All content processing happens in real-time
* Please review the privacy policies of your chosen AI provider

By using this plugin, you acknowledge that blog content and comments will be processed by external AI services.

= Third Party Services =

This plugin integrates with the following external services:

**OpenAI API**
* Service: https://openai.com/
* Privacy Policy: https://openai.com/policies/privacy-policy
* Terms of Service: https://openai.com/policies/terms-of-use

**Groq API**
* Service: https://groq.com/
* Privacy Policy: https://groq.com/privacy-policy
* Terms of Service: https://groq.com/terms/

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/filtah` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Settings → Filtah to configure your AI provider and API key.
4. Choose your preferred AI provider (OpenAI or Groq) and enter your API key.
5. Select an AI model and configure your reply settings.

== Frequently Asked Questions ==

= Do I need an API key? =

Yes, you need an API key from either OpenAI or Groq to use this plugin. Both services offer free tiers with paid upgrades for higher usage.

= Will this work with any comment system? =

Filtah works with WordPress's built-in comment system. It may not be compatible with third-party comment plugins.

= Can I edit the AI-generated replies? =

Yes, all AI-generated replies appear as regular WordPress comments and can be edited, moderated, or deleted through your WordPress admin.

= Does this plugin store my data? =

The plugin only stores comment metadata in your WordPress database. Your blog content and comments are sent to AI providers for processing but are not stored by this plugin.

= What happens if the AI service is unavailable? =

If the AI service is unavailable, the plugin will fail silently and no automatic reply will be generated. Regular comments will continue to work normally.

== Screenshots ==

1. Main settings page with AI provider selection
2. Comment management showing AI-generated replies
3. Bulk processing interface

== Changelog ==

= 1.0.1 =
* Fixed function naming prefixes for WordPress standards compatibility
* Added direct file access protection to PHP files
* Updated contributor information
* General code improvements

= 1.0.0 =
* Initial release
* Multi-provider support (OpenAI/Groq)
* Multiple model options
* Automatic comment replies
* Bulk processing
* Rate limiting
* Error logging
* Multilingual support



