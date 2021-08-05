
/**
 * This class generated css style which can automatically be injected into a given element.
 * This is not needed in unicode or image mode, only in css mode.
 *
 * @author Wolfgang Stöttinger
 */
import $ from 'jquery';
import Emoji from 'EmojiUtil';
import EmojiArea from 'EmojiArea';

export default class EmojiStyleGenerator {

  static createImageStyles(options = {}) {
    options = $.extend({}, EmojiArea.DEFAULTS, typeof options === 'object' && options);

    const iconSize = options.iconSize;
    const assetPath = options.assetPath;

    let style = '';
    // with before pseudo doesn't work with selection
    // style += '.emoji { font-size: 0; }.emoji::before{display: inline-block;content: \'\';width: ' + iconSize + 'px;height: ' + iconSize + 'px;}';
    // style += '.emoji{color: transparent;}.emoji::selection{color: transparent; background-color:highlight}';

    for (let g = 0; g < Emoji.groups.length; g++) {
      const group = Emoji.groups[g];
      const d = group.dimensions;

      for (let e = 0; e < group.items.length; e++) {
        const key = group.items[e];
        const emojiData = Emoji.data[key];
        if (!emojiData)
          continue;
        const alias = emojiData[Emoji.EMOJI_ALIASES];
        if (alias) {
          const row = e / d[0] | 0;
          const col = e % d[0];
          style += '.emoji-' + alias + '{'
            + 'background: url(\'' + assetPath + '/' + group.sprite + '\') '
            + (-iconSize * col) + 'px '
            + (-iconSize * row) + 'px no-repeat;'
            + 'background-size: ' + (d[0] * iconSize) + 'px ' + (d[1] * iconSize) + 'px;'
            + '}';
        }
      }
    }

    return style;
  }

  static injectImageStyles(element, options) {
    element = element || 'head';
    $('<style type="text/css">' + EmojiStyleGenerator.createImageStyles(options) + '</style>').appendTo(element);
  }
}
