define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/textarea',
    'Mirasvit_Sorting/js/lib/codemirror/codemirror',
    'Mirasvit_Sorting/js/lib/codemirror/spreadsheet'
], function ($, _, Textarea, CodeMirror) {
    'use strict';

    return Textarea.extend({
        defaults: {
            elementTmpl: 'Mirasvit_Sorting/ui/form/element/formula',
        },

        initEditor: function (textarea) {
            let self = this

            self.editor = CodeMirror.fromTextArea(
                textarea,
                {
                    lineNumbers: false,
                    matchBrackets: true,
                    mode: "text/x-spreadsheet",
                    indentUnit: 4,
                    indentWithTabs: false,
                    viewportMargin: Infinity,
                    styleActiveLine: true,
                    tabSize: 4
                }
            );

            // single-line editor
            self.editor.setSize(null, self.editor.defaultTextHeight() + 2 * 4);

            self.editor.on("beforeChange", function(instance, change) {
                var newtext = change.text.join("").replace(/\n/g, ""); // remove ALL \n !
                change.update(change.from, change.to, [newtext]);
                return true;
            });

            $(".CodeMirror-scroll").css('overflow', 'hidden');

            self.editor.on(
                'changes',
                self.listenEditorChanges.bind(self)
            )

            return this;
        },

        listenEditorChanges: function (editor) {
            $(".CodeMirror-hscrollbar").css('display', 'none');
            this.value(editor.getValue());
        },

        setEditorValue: function (newValue) {
            if (typeof this.editor !== 'undefined' &&
                newValue !== this.editor.getValue()
            ) {
                this.editor.setValue(newValue);
            }
        },

        initObservable: function () {
            this._super();
            this.value.subscribe(this.setEditorValue.bind(this));

            return this;
        },
    })
});
