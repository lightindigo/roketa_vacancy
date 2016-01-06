<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'

//        var_dump(Yii::app()->request->getPost('rqwe'));

        $model = Yii::app()->db->createCommand()
                ->select('*')
                ->from('purchases')
                ->queryRow();

        //$data = self::actionGetData();

		$this->render('index'
//            ,array('data'=>$data)
        );
	}

    /**
     * @return mixed
     */
    public function actionGetData($limited = true,$date_begin = null, $date_end = null) {

        $page_rows = 20;
        $page_days = 7;

        if($date_begin == null)
            $date_begin = Yii::app()->request->getPost('date_begin') == NULL ? date('Y-m-d',0) : Yii::app()->request->getPost('date_begin') ;
        if($date_end == null)
            $date_end = Yii::app()->request->getPost('date_end') == NULL ? date('Y-m-d') : Yii::app()->request->getPost('date_end');

        $hoffset = Yii::app()->request->getPost('hoffset') == NULL ? 0 : Yii::app()->request->getPost('hoffset');
        $voffset = Yii::app()->request->getPost('voffset') == NULL ? 0 : Yii::app()->request->getPost('voffset');

        if(!self::checkdate($date_begin))
            self::actionError('Некорректная дата начала периода');

        if(!self::checkdate($date_end))
            self::actionError('Некорректная дата начала периода');

        $hoffset = (int) $hoffset;
        $voffset = (int) $voffset;

        if(!is_integer($hoffset)){
            $hoffset = 0;
        }
        if(!is_integer($voffset)){
            $voffset = 0;
        }

        $voffset = $voffset * $page_rows;

        $interv = new DateInterval("P".($hoffset*$page_days)."D");
        $date_begin = $date_begin != 0 ? new DateTime($date_begin) : DateTime::createFromFormat('Y-m-d', $date_begin);
        $date_end = $date_end != 0 ? new DateTime($date_end) : DateTime::createFromFormat('Y-m-d', $date_end);


        $offset_begin = clone($date_begin);
        $offset_end = null;

        if($limited) {
            $offset_begin = $offset_begin->add($interv);
            $offset_end = clone($offset_begin);
            $interv = new DateInterval("P" . $page_days . "D");
            $offset_end = $offset_end->add($interv);
        }
        else
        {
            $offset_end = clone($date_end);
        }


        $model = Yii::app()->db->createCommand()
            ->select("pm_id,price")
            ->from('purchases')
            ->group('pm_id, price')
            ->where("ts_day_start >=".$date_begin->getTimestamp()." AND ts_day_start <= ".$date_end->getTimestamp());

        $sql = $model->text;

        $model = Yii::app()->db->createCommand()
            ->select("pm_id,price")
            ->from('purchases')
            ->group('pm_id, price')
            ->where("ts_day_start >=".$date_begin->getTimestamp()." AND ts_day_start <= ".$date_end->getTimestamp());

        if($limited)
            $model->offset($voffset)->limit($page_rows);

        $model = $model->queryAll();

        $pairs = $model;

        if(!isset($model[0]))
            self::ajaxResponse('ok','empty');

        $min_pm = $model[0]['pm_id'];
        $max_pm = $model[count($model)-1]['pm_id'];
        $max_price = $model[count($model)-1]['price'];


        $model = Yii::app()
            ->db
            ->createCommand("select count(*) as total FROM ($sql) as tab")
            ->queryRow();
        $total = $model['total'];


        $model = Yii::app()->db->createCommand()
            ->select("pm_id,price,date_format(from_unixtime(ts_day_start),'%d.%m.%Y') as date,SUM(a_count*price) as income")
            ->from('purchases')
            ->group('pm_id, price, ts_day_start')
            ->where("((pm_id BETWEEN $min_pm AND $max_pm) OR (pm_id = $max_pm AND price <= $max_price)) AND ts_day_start >=".$offset_begin->getTimestamp()." AND ts_day_start <= ".$offset_end->getTimestamp());

        $model = $model->queryAll();

        $reconf_array = array();

        foreach($pairs as $pair){
            if(!isset($reconf_array[$pair['pm_id']]))
                $reconf_array[$pair['pm_id']] = array();

            if(!isset($reconf_array[$pair['pm_id']][$pair['price']]))
                $reconf_array[$pair['pm_id']][$pair['price']] = array();
        }

        foreach($model as $record){
            if(!isset($reconf_array[$record['pm_id']]))
                $reconf_array[$record['pm_id']] = array();

            if(!isset($reconf_array[$record['pm_id']][$record['price']]))
                $reconf_array[$record['pm_id']][$record['price']] = array();

            $reconf_array[$record['pm_id']][$record['price']][$record['date']] = $record['income'];
        }

        $data = array(
            'result' => $reconf_array,
//                    'test_dates' => array(
//                        'offset_begin' => $offset_begin->format('d.m.Y'),
//                        'offset_end' => $offset_end->format('d.m.Y'),
//                        'date_begin' => $date_begin->format('d.m.Y'),
//                        'date_end' => $date_end->format('d.m.Y'),
//                    ),
            'offset_begin' => $offset_begin->getTimestamp(),
            'offset_end' => $offset_end->getTimestamp(),
            'date_begin' => $date_begin->getTimestamp(),
            'date_end' => $date_end->getTimestamp(),
            'total' => $total
        );

        if(Yii::app()->request->isAjaxRequest){

            self::ajaxResponse('ok','',
                $data
            );
        }
        else{
            return $data;
        }


    }

    public function actionGenerateTable(){

        error_reporting(E_ALL);
        ini_set('display_errors',true);

        $date_min = '1.01.2014';
        $date_max = '31.12.2015';

        $step = 100;

        $date_min = strtotime($date_min);
        $date_max = strtotime($date_max);

//        echo $date_min."<br>";
//        echo $date_max."<br>";

        for( $i = 0; $i < 10000; $i++){
            $new_date = rand($date_min,$date_max);
            $new_date = date('d.m.Y 00:00:00',$new_date);

            $price = ((int) rand(1,10)) * $step;

            $amount = rand(0,30);

            $pm_id = rand(1,50);

            echo $new_date."<br>";

            $new_date = strtotime($new_date);

            Yii::app()->db->createCommand("INSERT INTO purchases VALUES('$new_date','$pm_id','$price','$amount')")->query();
        }
    }

    public function actionGenerateFile(){

        $date_begin = Yii::app()->request->getParam('date_begin') == NULL ? date('Y-m-d',0) : Yii::app()->request->getParam('date_begin') ;
        $date_end = Yii::app()->request->getParam('date_end') == NULL ? date('Y-m-d') : Yii::app()->request->getParam('date_end');

//        var_dump($date_begin);
//        var_dump($date_end);
//
//        die;
        $result = self::actionGetData(false,$date_begin,$date_end);

        $date_begin = DateTime::createFromFormat('Y-m-d', $date_begin);
        $date_end = DateTime::createFromFormat('Y-m-d', $date_end);

        $date_begin_orig = clone($date_begin);

        $dates = array();
        $interv = new DateInterval('P1D');


        while($date_begin <= $date_end){
            $dates[] = $date_begin->format('d.m.Y');
            $date_begin->add($interv);
        }

        $head = array('pm_id','price');

        $head = array_merge($head,$dates);

        $file_array =array();
//        var_dump($result);
        foreach($result['result'] as $pm_id => $prices){

            foreach($prices as $price => $days)
            {
                $file_array[$pm_id.$price][] = $pm_id;
                $file_array[$pm_id.$price][] = $price;
                foreach($dates as $date){
                    if(isset($days[$date]))
                        $file_array[$pm_id.$price][] = $days[$date];
                    else
                        $file_array[$pm_id.$price][] = 0;
                }
            }
        }

        $filename = "report ".$date_begin_orig->format('Y-m-d')."-".$date_end->format('Y-m-d').".xls";

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel;");
        header("Pragma: no-cache");
        header("Expires: 0");
        $out = fopen("php://output", 'w');
        fputcsv($out, $head,"\t");
        foreach ($file_array as $data)
        {
            fputcsv($out, $data,"\t");
        }
        fclose($out);

//        var_dump($dates);
//        var_dump($file_array);
//        var_dump($file_array);
    }

    public function ajaxResponse($status,$message = null,$data = null){
        $response = array(
            'status' => $status == null ? 'ok' : $status,
            'message' => $message,
            'data' => $data
        );

        echo json_encode($response);
        die;
    }

    public function ajaxError($message){
        self::ajaxResponse('error',$message);
    }

    public function checkDate($date){
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
}