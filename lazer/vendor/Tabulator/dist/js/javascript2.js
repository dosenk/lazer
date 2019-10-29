function button_send (){ //plain text value
    return '<input type="button" value="send">';
};
function button_clear (){ //plain text value
    return '<input type="button" value="clear">';
};


function dateedit (cell, formatterParams, onRendered) {

    let row = cell.getRow();
    let selector = 'datetimepicker_' + row.getIndex() + '_' + formatterParams;
    let div = document.createElement("div");
    div.classList.add(selector);
    let innerHTML = '<input>';
    div.insertAdjacentHTML('afterbegin', innerHTML);
    let div_class = '.'+selector;
    let $d7input;
    let cell_value = '';

    onRendered(function(){
        let $dropdown;
        let datatime;


        $d7input = $('input', div_class);
        $d7input.focus(function() {
                $('.dropdown', div_class).remove();
                $dropdown = $('<div class="dropdown"/>').appendTo(div_class);
                
                        $dropdown.datetimepicker({
                            date: $d7input.data('value') || new Date(),
                            firstDayOfWeek: 1,
                            viewMode: 'YMDHMS',
                            onDateChange: function(){
                                // debugger;
                                $d7input.val(this.getText('YYYY-MM-DD HH:mm'));
                                // datatime = this.getValue();
                                $d7input.data('value', this.getValue());

                                // $dropdown.remove();
                            },
                            onOk: function () {
                                // $d7input.val(this.getText('YYYY-MM-DD HH:mm'));
                                // $d7input.data('value', this.getValue());
                                // $dropdown.remove();
                                cell_value = this.getText('YYYY-MM-DD HH:mm');
                                // cell.setValue(cell_value);
                                // console.log(cell);
                                this.destroy();
                            },
                            onClose: function () {
                                console.log(this);
                            }
                        })

            });

        // $d7input.focusout(function () {
        //     $('.dropdown', div_class).remove();
        // })
    });
    // div.insertAdjacentText('afterbegin', cell_value);

    return div;
};