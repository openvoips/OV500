# jquery.emojiarea.js

This jquery plugin adds emoji support to textareas, supports colon aliases (e.g. :smile:) and features an emoji picker to insert emojis directly into the textarea. 
Currently three different usage modes are supported: unicode, css and image.  

This library is inspired by [OneSignal/emoji-picker](https://github.com/OneSignal/emoji-picker) and [diy/jquery-emojiarea](https://github.com/diy/jquery-emojiarea). It has been rewritten from scratch with easy usability from the developer perspective in mind.

Out comes a much cleaner, smaller and more flexible emoji library than all others I know of.

## Installation

```
npm i --save jquery.emojiarea.js
```

### Versatile usage
Currently three different usage modes are supported: 
 - unicode: plain unicode emojis are inserted, no markup.
 - css: small tags with little footprint are inserted in the markup. the image is set in the css
 - image: the whole image tag (including style) is inserted into the markup

Currently about 850 distinct emoticons (excluding variations) are supported, these are currently sorted in 5 groups where each group has its own sprite png file. 

The sprites and groups can be easily customized. 

### Features in development
 - automatically replace ascii emoticons (currently only colon aliases supported e.g. :smlie: => 😀)
 - generate sprites in build process, or maybe integrate with [iamcal/emoji-data](https://github.com/iamcal/emoji-data)
 - update emojis according to [unicode technical standard #51](http://unicode.org/reports/tr51/) with current version 5 of unicode.org Emoji List ([list](http://unicode.org/emoji/charts/emoji-list.html) and [full chart](http://unicode.org/emoji/charts/full-emoji-list.html)) 
 - create class hierarchy for unicode / css / image modes to reduce the code footprint for simpler versions 
 - autocomplete (with dropdown) for colon aliases (also modular)
 
### Known Bugs/Todos
 - fix caret position after paste and/or insert
 - workaround for css and image mode in edge
 