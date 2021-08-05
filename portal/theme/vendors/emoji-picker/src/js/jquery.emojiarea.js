/**
 * This is the entry point for the library
 *
 * @author Wolfgang Stöttinger
 */

import $ from 'jquery';
import generatePlugin from './generate-plugin';
import EmojiStyleGenerator from 'EmojiStyleGenerator'
import EmojiArea from 'EmojiArea';

generatePlugin('emojiarea', EmojiArea);

/**
 * call auto initialization.
 */
$(() => {
  $('[data-emoji-inject-style]').each((i, e) => {EmojiStyleGenerator.injectImageStyles(e); });
  $('[data-emojiarea]').emojiarea();
});
