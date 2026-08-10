## Translation memory

The translation memory stores every source text that was translated once, together with
its translation into one target language. It is shared over the whole application, so
every record holding the same source text receives the same wording, and no text has to
be paid for twice.

### Enable

```yaml
# config/packages/enhavo_translation.yaml
enhavo_translation:
    translation_client:
        client: Enhavo\Bundle\TranslationBundle\Client\ChainTranslationClient
        memory:
            enabled: true
```

`MemoryTranslationClient` then decorates whatever `client` points to. None of the clients
knows about the memory, and switching the client keeps the memory in place.

Add the routes to reach the admin module:

```yaml
# config/routes/enhavo_translation.yaml
enhavo_translation_admin:
    resource: "@EnhavoTranslationBundle/Resources/config/routes/admin/*"
    prefix: /admin/translation

enhavo_translation_admin_api:
    resource: "@EnhavoTranslationBundle/Resources/config/routes/admin_api/*"
    prefix: /admin/api/translation
```

The entity `Enhavo\Bundle\TranslationBundle\Entity\TranslationMemory` needs a migration.
It is mapped whether the memory is enabled or not.

### Review workflow

Every entry carries a status:

| Status | Meaning |
|---|---|
| `open` | Machine output, not reviewed yet |
| `feedback` | Marked as needing discussion, not protected |
| `reviewed` | Approved by a human, never overwritten by an automatic run |

Everything the system writes gets the status `open`, and an entry with an empty
translation is always set back to `open`. So the `open` filter of the grid is the work
list of the editorial team.

These things can be done with the entries in the admin module:

- the batches **mark as reviewed / feedback / open** move entries through the workflow,
- **suggest translation** asks the client for a fresh proposal and writes it into the
  entry only, without touching the records that use it. Available as an action on the
  open entry and as a batch on a selection,
- the action **push translation** writes a corrected entry onto every translation that
  holds the same source text.

Both suggest variants skip entries with the status `reviewed`, so approved wording is
never replaced by machine output.

### Options

The memory is steered with runtime options, either on the endpoint or per property node.

| Option | Default | Effect |
|---|---|---|
| `use_memory` | `true` | Look into the memory before calling the client |
| `memory_only` | `false` | Never call the client, only serve and collect. Useful for a dry run or to restore a record from translations that were already paid for |
| `overwrite` | `false` | Replace the translation on the record and the stored entry. Reviewed entries are served instead and stay untouched |
| `ignore_status` | `true` | Reuse entries of any status. With `false` only reviewed entries are reused |
| `store_only` | `false` | Write to the memory but return nothing, so the caller keeps its value |

Two routes on the same endpoint type give a "Translate" and a "Re-translate" button:

```yaml
# config/routes/admin_api/article.yaml
app_admin_api_article_translate:
    path: /article/translate/resource
    defaults:
        _expose: admin_api
        _endpoint:
            type: translate_resource
            resource: app.article

app_admin_api_article_translate_overwrite:
    path: /article/translate-overwrite/resource
    defaults:
        _expose: admin_api
        _endpoint:
            type: translate_resource
            resource: app.article
            overwrite: true
            use_memory: false
```

```yaml
# config/resources/article.yaml
enhavo_resource:
    inputs:
        enhavo_article.article:
            actions:
                translate:
                    type: translate
                    route: app_admin_api_article_translate
                translate_overwrite:
                    type: translate
                    route: app_admin_api_article_translate_overwrite
                    overwrite: true
```

`overwrite` on the action only selects the label and the confirm dialog. What really
happens is decided by the endpoint the route points to.

### Restricting the target locales

Translating into a locale a record is not published in is paid waste. A resource can
narrow the set of locales down by implementing `TranslationLocalesAwareInterface`:

```php
use Enhavo\Bundle\TranslationBundle\Model\TranslationLocalesAwareInterface;

class Article implements TranslationLocalesAwareInterface
{
    public function getTranslationLocales(): array
    {
        return $this->availableLocales;
    }
}
```

### Calling it from code

```php
$this->translationManager->applyAutoTranslation($resource, 'de', null, $resource, [
    'overwrite' => true,
    'usage' => 'article:5',
]);
```

The fourth parameter stays the prompt context, the fifth carries the runtime options.
They win over the options of the property node and are handed down to nested resources.

### Upgrade notes

- `TranslationManager::applyAutoTranslation()` and `Translation::autoTranslate()` take an
  additional `array $options` as last parameter. `TranslationTypeInterface` is unchanged,
  the runtime options are merged into the options a type receives, so custom translation
  types keep working.
- `ContextProviderInterface::getText()` and `getFiles()` take an optional `?string $locale`.
  Custom providers have to add the parameter.
- `TextTranslationType` no longer writes a `null` the client returned over an existing
  translation, and no longer calls the client with an empty source value. Both cases used
  to destroy translations when `overwrite` was set.
- `SaveActionType` has a new option `morph` (default `true`). Set it to `false` where DOM
  morphing after the save breaks the state of a tree like form, e.g. a navigation.
- `TranslateActionType` defaults `enabled` to `expr:resource.getId() !== null`, because a
  record that was never saved has nothing to translate.
- `ClaudeTranslationClient` returns `null` when the model answers `--invalid-request--`,
  instead of storing that answer as a translation.