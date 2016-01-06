<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>
<!--<h1>Welcome to <i>--><?php //echo CHtml::encode(Yii::app()->name); ?><!--</i></h1>-->
<!---->
<!--<p>Congratulations! You have successfully created your Yii application.</p>-->
<!---->
<!--<p>You may change the content of this page by modifying the following two files:</p>-->
<!--<ul>-->
<!--	<li>View file: <code>--><?php //echo __FILE__; ?><!--</code></li>-->
<!--	<li>Layout file: <code>--><?php //echo $this->getLayoutFile('main'); ?><!--</code></li>-->
<!--</ul>-->
<!---->
<!--<p>For more details on how to further develop this application, please read-->
<!--the <a href="http://www.yiiframework.com/doc/">documentation</a>.-->
<!--Feel free to ask in the <a href="http://www.yiiframework.com/forum/">forum</a>,-->
<!--should you have any questions.</p>-->

<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>assets/bootstrap-datepicker/css/bootstrap-datepicker.min.css">
<script src="<?php echo Yii::app()->request->baseUrl; ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>js/main.js"></script>
<script src="<?php echo Yii::app()->request->baseUrl; ?>assets/bootstrap-datepicker/locales/bootstrap-datepicker.ru.min.js"></script>
<div class="row">
    <h1>Запросить данные</h1>

    <table id="request_form">
        <tbody>
<!--        <tr>-->
<!--            <td style="width: 20px"><label>C</label></td>-->
<!--            <td id="date_begin" >-->
<!--<!--                <input type="text" id="date_begin" class="form-control" >-->
<!--                <div class="input-group date">-->
<!--                <input type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>-->
<!--                </div>-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td colspan="2" class="error" id="datebeg_error">-->
<!--                <span>-->
<!--                    Текст ошибки-->
<!--                </span>-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td><label>по</label></td>-->
<!--            <td id="date_end">-->
<!--<!--                <input type="text" id="date_end" class="form-control">-->
<!--                <div class="input-group date">-->
<!--                    <input type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>-->
<!--                </div>-->
<!--            </td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td colspan="2" class="error" id="dateend_error">-->
<!--                <span>-->
<!--                    Текст ошибки-->
<!--                </span>-->
<!--            </td>-->
<!--        </tr>-->
        <tr>
            <td><button id="request_data" class="btn btn-success">Запросить данные</button></td>
            <td><button id="downlad_data" class="btn btn-info">Скачать отчет</button></td>
        </tr>
        <tr>
            <td><button id="prev_date" class="btn btn-success disabled" disabled="disabled">Предыдущие даты</button></td>
            <td><button id="next_date" class="btn btn-info">Следующие даты</button></td>
        </tr>
        <tr>
            <td>
                Страницы по датам
            </td>
            <td id="datepages">
                <select class="form-control">

                </select>
            </td>
        </tr>
        <tr>
            <td><button id="prev_pm" class="btn btn-success disabled" disabled="disabled">Предыдущие товары</button></td>
            <td><button id="next_pm" class="btn btn-info">Следующие товары</button></td>
        </tr>
        <tr>
            <td>
                Страницы по товарам
            </td>
            <td id="pmpages">
                <a class="page" href="#">1</a>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="input-group input-daterange">
    <input type="text" class="form-control" id="start_date" value="1.11.2015">
    <span class="input-group-addon">to</span>
    <input type="text" class="form-control" id="end_date" value="1.01.2016">
</div>

<!--<pre>-->
<?
//    if($data)
//        var_dump($data);
?>
<!--    </pre>-->
<div class="row">
    <table id="result_table" class="table">
        <thead>
        <tr id="table_head">
            <th>pm_id</th>
            <th>price</th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
<!--    <table id="result_table" class="table">-->
<!--        <thead>-->
<!--        <tr>-->
<!--            <th>pm_id</th>-->
<!--            <th>price</th>-->
<!--        </tr>-->
<!--        </thead>-->
<!--        <tbody>-->
<!---->
<!--        </tbody>-->
<!--    </table>-->
</div>

<script>
    var vpage = 1;
    var hpage = 1;

    $('#date_begin .input-group.date,#date_end .input-group.date').datepicker({
        language: "ru",
        format: "dd.mm.yyyy",
        todayBtn: true,
        forceParse: false,
        autoclose: true
    });

    $('.input-daterange').datepicker({
        language: "ru",
        keyboardNavigation: false,
        autoclose: true,
        forceParse:true,
        todayHighlight: true,
        inputs: $('.input-daterange input')
    })

</script>