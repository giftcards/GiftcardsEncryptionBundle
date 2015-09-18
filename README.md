GiftcardsEncryptionBundle [![Build Status](https://travis-ci.org/giftcards/GiftcardsEncryptionBundle.svg)](https://travis-ci.org/giftcards/GiftcardsEncryptionBundle)
-------------------------

Bundle to integrate the encryption lib into symfony

default configuration

```yaml
# Default configuration for extension with alias: "giftcards_encryption"ftcards_encryption --ansi
giftcards_encryption:
    cipher_texts:
        rotators:

            # Prototype
            name:
                type:                 ~ # Required
                options:              []
        serializers:
            type:                 ~ # Required
            options:              []
            priority:             0
        deserializers:
            type:                 ~ # Required
            options:              []
            priority:             0
    profiles:

        # Prototype
        name:
            cipher:               ~ # Required
            key_name:             ~ # Required
    keys:
        sources:
            type:                 ~ # Required
            options:              []
            prefix:               ''
            add_circular_guard:   false
        cache:                false
        map:

            # Prototype
            name:                 ~
        fallbacks:

            # Prototype
            name:                 []
        combine:

            # Prototype
            name:
                left:                 ~ # Required
                right:                ~ # Required
    default_profile:      null
    doctrine:
        encrypted_properties:
            enabled:              true
            connections:

                # Default:
                - default


```

Doctrine
--------

you can configure the the connections to assign the encrypted properties listener by setting the `doctrine.encrypted_properties.connections` with an
array of the names of the connections you configured in the doctrine configs that you want the encrypted properties feature to be available for.
if you are not using doctrine set `doctrine.encrypted_properties.enabled` to false.