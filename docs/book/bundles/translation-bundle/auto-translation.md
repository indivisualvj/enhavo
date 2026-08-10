## Auto translation

Auto translation allows you to automatically translate your content using external translation services
like DeepL or Claude. The system translates all properties that are marked as translatable and support auto-translation.

### Clients

To enable auto translation, you need to configure at least one translation client.
You can provide context to the translation client to improve translation quality.
The context is passed along with each translation request.

```yaml
# config/packages/enhavo_translation.yaml
enhavo_translation:
    translation_client:
        context:
            provider: Enhavo\Bundle\TranslationBundle\Client\ConfigContextProvider
            text: 'This is a website about cooking recipes. Use informal language.'
            files:
                - 'translations/context.txt'
        client: Enhavo\Bundle\TranslationBundle\Client\DeeplTranslationClient
        memory:
            enabled: true
        deepl:
            api_key: '%env(DEEPL_API_KEY)%'
            glossary_id: 'your-glossary-id'
        claude:
            api_key: '%env(CLAUDE_API_KEY)%'
            model: 'claude-haiku-4-5-20251001'
            timeout: 600
            max_tokens: 4096
        chain:
            clients:
                - Enhavo\Bundle\TranslationBundle\Client\ClaudeTranslationClient
                - Enhavo\Bundle\TranslationBundle\Client\UrlTranslationClient
        url:
            domains:
                - 'example.com'
```

Terminology and style guidelines can differ per target locale. Everything under `locales`
is added to the global `text` and `files` when translating into that locale.

```yaml
enhavo_translation:
    translation_client:
        context:
            files:
                - 'translations/glossary.csv'
            locales:
                de:
                    text: 'Address the reader with "Sie".'
                    files:
                        - 'translations/guidelines.de.md'
```

With `memory.enabled` the [translation memory](#translation-memory) is put in front of
the configured client, so no text is translated and paid for twice.


### Endpoint and Action

To allow users to trigger auto translation from the admin interface, you need to add
a route with the `translate_resource` endpoint and a `translate` action to the input configuration.

```yaml
# config/routes/admin_api/article.yaml
app_admin_api_article_translate_resource:
    path: /article/translate/resource
    defaults:
        _expose: admin_api
        _endpoint:
            type: translate_resource
            resource: app.article
```

```yaml
# config/resources/article.yaml
enhavo_resource:
    inputs:
        enhavo_article.article:
            actions:
                translate:
                    type: translate
                    route: app_admin_api_article_translate_resource
```

This adds a translate button to the resource form. When clicked, it saves the resource
and translates all translatable properties into every configured locale.

### Console command

You can also trigger auto translation from the command line.

```bash
bin/console translation:auto-translate <resource> <id> <locale>
```

For example:

```bash
bin/console translation:auto-translate app.article 5 de
```
