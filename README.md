GiftcardsEncryptionBundle [![Build Status](https://travis-ci.org/giftcards/GiftcardsEncryptionBundle.svg)](https://travis-ci.org/giftcards/GiftcardsEncryptionBundle)
-------------------------

Bundle to integrate the encryption lib into symfony

default configuration

```yaml
# Default configuration for extension with alias: "giftcards_encryption"
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

```
