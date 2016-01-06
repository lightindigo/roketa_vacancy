var test_days;
var test_result;

var periods = [];

var new_cond = {
    date_begin: null,
    date_end: null,
    vpage: 0,
    hpage: 0
};

var current_cond = {
    date_begin: null,
    date_end: null,
    vpage: 0,
    hpage: 0,
    offset_begin: 0,
    offset_end: 0
};

$(function(){
    $('.input-daterange').datepicker().on('changeDate',
        function(){
            new_cond.date_begin = $("#start_date").val();
            new_cond.date_end = $("#end_date").val();
        }
    );

    $('#prev_date').on('click',function(){
        --current_cond.hpage;
        if(current_cond.hpage == 0) {
            $('#prev_date').attr('disabled', 'disabled');
            $('#prev_date').addClass('disabled');
        }

        if(current_cond.hpage < (periods.length-1)) {
            $('#next_date').removeAttr('disabled');
            $('#next_date').removeClass('disabled');
        }
        $('#datepages select option')[current_cond.hpage].selected=true
        send_request(false);
    });
    $('#next_date').on('click',function(){
        ++current_cond.hpage;
        if(current_cond.hpage != 0) {
            $('#prev_date').removeAttr('disabled');
            $('#prev_date').removeClass('disabled');
        }
        if(current_cond.hpage == (periods.length-1) ) {
            $('#next_date').attr('disabled', 'disabled');
            $('#next_date').addClass('disabled');
        }
        $('#datepages select option')[current_cond.hpage].selected=true
        send_request(false);
    });

    $('#prev_pm').on('click',function(){
        --current_cond.vpage;
        if(current_cond.vpage == 0) {
            $('#prev_pm').attr('disabled', 'disabled');
            $('#prev_pm').addClass('disabled');
        }

        if(current_cond.vpage < current_cond.pages) {
            $('#next_pm').removeAttr('disabled');
            $('#next_pm').removeClass('disabled');
        }

        $('.page_but.active').removeClass('active');
        $('.page_but[data-page='+current_cond.vpage+']').addClass('active');


        send_request(false);
    });
    $('#next_pm').on('click',function(){
        ++current_cond.vpage;
        if(current_cond.vpage != 0) {
            $('#prev_pm').removeAttr('disabled');
            $('#prev_pm').removeClass('disabled');
        }

        if(current_cond.vpage == (current_cond.pages-1)) {
            $('#next_pm').attr('disabled', 'disabled');
            $('#next_pm').addClass('disabled');
        }

        $('.page_but.active').removeClass('active');
        $('.page_but[data-page='+current_cond.vpage+']').addClass('active');

        send_request(false);
    });


    $('#request_data').on('click',function(){
        send_request(true);
    });
});

function send_request(is_new_request){
    $('#dummy-modal').modal('show');
    new_cond.date_begin = $("#start_date").val();
    var date = new_cond.date_begin.split('.');
    date = new Date(date[2],(date[1]-1),date[0]);
    new_cond.date_begin = date;
    new_cond.date_end = $("#end_date").val();
    var date = new_cond.date_end.split('.');
    date = new Date(date[2],(date[1]-1),date[0]);
    new_cond.date_end = date;

    date_obj = is_new_request ? new_cond : current_cond;

    $.post('/site/getdata',{
        date_begin: date_obj.date_begin.getDate()+"."+(date_obj.date_begin.getMonth()+1)+"."+date_obj.date_begin.getFullYear(),
        date_end: date_obj.date_end.getDate()+"."+(date_obj.date_end.getMonth()+1)+"."+date_obj.date_end.getFullYear(),
        voffset: date_obj.vpage,
        hoffset: date_obj.hpage
    })
        .done(function(){
            $('#dummy-modal').modal('hide');
        })
        .success(function(response){
            response = JSON.parse(response);

            if(is_new_request) {

                $('#download_data button').removeClass('disabled');
                $('#download_data button').removeAttr('disabled');
                $('#download_data a').attr('href','/site/generateFile?date_begin='+encodeURI(date_obj.date_begin.getFullYear()+"-"+(date_obj.date_begin.getMonth()+1)+"-"+date_obj.date_begin.getDate())+'&date_end='+encodeURI(date_obj.date_end.getFullYear()+"-"+(date_obj.date_end.getMonth()+1)+"-"+date_obj.date_end.getDate()));
                periods = [];

                var timeDiff = Math.abs(date_obj.date_end.getTime() - date_obj.date_begin.getTime());
                var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

                current_cond = Object.create(new_cond);

                periods_count = parseInt(diffDays/7)+1;

                //alert(periods_count);
                if(periods_count == 1)
                {
                    $('#prev_date').attr('disabled', 'disabled');
                    $('#prev_date').addClass('disabled');
                    $('#next_date').attr('disabled', 'disabled');
                    $('#next_date').addClass('disabled');
                }

                else{
                    $('#next_date').removeAttr('disabled');
                    $('#next_date').removeClass('disabled');
                }

                $('#datepages select').on('change',function(){
                    current_cond.hpage = this.value;
                    send_request(false);

                    if(current_cond.hpage == 0) {
                        $('#prev_date').attr('disabled', 'disabled');
                        $('#prev_date').addClass('disabled');
                    }

                    if(current_cond.hpage < (periods.length-1)) {
                        $('#next_date').removeAttr('disabled');
                        $('#next_date').removeClass('disabled');
                    }

                    if(current_cond.hpage != 0) {
                        $('#prev_date').removeAttr('disabled');
                        $('#prev_date').removeClass('disabled');
                    }
                    if(current_cond.hpage == (periods.length-1) ) {
                        $('#next_date').attr('disabled', 'disabled');
                        $('#next_date').addClass('disabled');
                    }
                });

                $('#datepages select').html('');

                for(var i = 0; i < periods_count; i++){
                    date1 = new Date(date_obj.date_begin.getTime());

                    date1.setDate(date1.getDate()+i*7);

                    date2 = new Date(date1.getTime());

                    date2.setDate(date2.getDate()+7);

                    if(date2 > date_obj.date_end)
                    {
                        date2 = new Date(date1.getTime());
                        console.log(diffDays);
                        console.log(i*7);
                        date2.setDate(date2.getDate()+(diffDays - ( i * 7)));
                    }

                    console.log(date1.getDate()+'.'+(date1.getMonth()+1)+'.'+date1.getFullYear()+' - '+date2.getDate()+'.'+(date2.getMonth()+1)+'.'+date2.getFullYear());
                    periods.push(date1.getDate()+'.'+(date1.getMonth()+1)+'.'+date1.getFullYear()+' - '+date2.getDate()+'.'+(date2.getMonth()+1)+'.'+date2.getFullYear());

                    var option = $('<option>');
                    option.val(periods.length-1);
                    option.text(periods[periods.length-1]);
                    $('#datepages select').append(option);
                }

                current_cond.total = response.data.total;

                var pages = Math.ceil(response.data.total/20);

                current_cond.pages = pages;

                if(pages == 1){
                    $('#prev_pm').attr('disabled', 'disabled');
                    $('#prev_pm').addClass('disabled');
                    $('#next_pm').attr('disabled', 'disabled');
                    $('#next_pm').addClass('disabled');
                }
                else
                {
                    $('#next_pm').removeAttr('disabled');
                    $('#next_pm').removeClass('disabled');
                }

                $('#pmpages').html('');

                for(var i = 0; i < pages; i++){

                    var new_link = $('<a>');
                    new_link.addClass('page_but');
                    if(i == 0)
                        new_link.addClass('active');
                    new_link.attr('href','#');
                    new_link.attr('data-page',i);
                    new_link.text(i+1);

                    //вертикальный пагинатор
                    new_link.on('click',function(){
                        current_cond.vpage = $(this).attr('data-page');

                        $('.page_but.active').removeClass('active');
                        $(this).addClass('active');

                        send_request(false);

                        if(current_cond.vpage == 0) {
                            $('#prev_pm').attr('disabled', 'disabled');
                            $('#prev_pm').addClass('disabled');
                        }

                        if(current_cond.vpage < current_cond.pages) {
                            $('#next_pm').removeAttr('disabled');
                            $('#next_pm').removeClass('disabled');
                        }

                        if(current_cond.vpage != 0) {
                            $('#prev_pm').removeAttr('disabled');
                            $('#prev_pm').removeClass('disabled');
                        }

                        if(current_cond.vpage == (current_cond.pages-1)) {
                            $('#next_pm').attr('disabled', 'disabled');
                            $('#next_pm').addClass('disabled');
                        }
                    });
                    $('#pmpages').append(new_link);
                }

            }

            current_cond.offset_begin = new Date(response.data.offset_begin * 1000);
            current_cond.offset_end = new Date(response.data.offset_end * 1000);

            //current_cond.date_begin = new Date(response.data.date_begin * 1000);
            //current_cond.date_end = new Date(response.data.date_end * 1000);

            var days = [];

            while((current_cond.offset_end >= current_cond.offset_begin) && (current_cond.offset_begin <= current_cond.date_end)){
                days.push((current_cond.offset_begin.getDate() < 10 ? "0"+current_cond.offset_begin.getDate() : current_cond.offset_begin.getDate())+"."+((current_cond.offset_begin.getMonth()+1) < 10 ? '0'+(current_cond.offset_begin.getMonth()+1) : (current_cond.offset_begin.getMonth()+1))+"."+current_cond.offset_begin.getFullYear());
                current_cond.offset_begin.setDate(current_cond.offset_begin.getDate()+1);
            }



            $('#table_head').html('');
            $('#result_table tbody').html('');

            test_days = days;

            var $tableHead = $('#table_head');
            $tableHead.html('');
            var new_elem = $('<th>');
            new_elem.text('pm_id');
            $tableHead.append(new_elem);
            var new_elem = $('<th>');
            new_elem.text('price');
            $tableHead.append(new_elem);

            for(date in days){
                var new_elem = $('<th>');
                new_elem.text(days[date]);
                $tableHead.append(new_elem);
            }

            var result = response.data.result;

            test_result = result;

            for(pm in result){

                for(price in result[pm])
                {

                    var new_row = $('<tr>');
                    var new_cell = $('<td>');
                    new_cell.text(pm);
                    new_row.append(new_cell);
                    var new_cell = $('<td>');
                    new_cell.text(price);
                    new_row.append(new_cell);

                    for(date in days)
                    {

                        var new_cell = $('<td>');
                        var check_obj = result[pm][price];

                        if(check_obj.hasOwnProperty(days[date]))
                            new_cell.text(check_obj[days[date]]);
                        else
                            new_cell.text(0);

                        new_row.append(new_cell);
                    }

                    var $resultTable = $('#result_table tbody');
                    $resultTable.html();
                    $resultTable.append(new_row);
                }
            }
        })

}