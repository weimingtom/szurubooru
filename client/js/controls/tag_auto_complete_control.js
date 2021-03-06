'use strict';

const tags = require('../tags.js');
const misc = require('../util/misc.js');
const AutoCompleteControl = require('./auto_complete_control.js');

class TagAutoCompleteControl extends AutoCompleteControl {
    constructor(input, options) {
        const minLengthForPartialSearch = 3;

        options = Object.assign({
            isTaggedWith: tag => false,
        }, options);

        options.getMatches = text => {
            const transform = x => x.toLowerCase();
            const match = text.length < minLengthForPartialSearch ?
                (a, b) => a.startsWith(b) :
                (a, b) => a.includes(b);
            text = transform(text);
            return Array.from(tags.getNameToTagMap().entries())
                .filter(kv => match(transform(kv[0]), text))
                .sort((kv1, kv2) => {
                    return kv2[1].usages - kv1[1].usages;
                })
                .map(kv => {
                    const origName = tags.getOriginalTagName(kv[0]);
                    const category = kv[1].category;
                    const usages = kv[1].usages;
                    let cssName = misc.makeCssName(category, 'tag');
                    if (options.isTaggedWith(kv[0])) {
                        cssName += ' disabled';
                    }
                    return {
                        caption: misc.unindent`
                            <span class="${cssName}">
                                ${misc.escapeHtml(origName)} (${usages})
                            </span>`,
                        value: origName,
                    };
                });
        };

        super(input, options);
    }
};

module.exports = TagAutoCompleteControl;
