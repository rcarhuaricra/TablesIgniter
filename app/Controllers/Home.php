<?php namespace App\Controllers;

use App\Models\HomeModel;
use ricv\RicvDataTable;

class Home extends BaseController{
  	
	public function index(){
		return view('Home');
	}

	public function firstTable(){
		$model = new HomeModel();
		$table = new RicvDataTable();
		$table->setTable($model->noticeTable())
			  ->setOutput(["id","title","date"]);
		return $table->getDatatable();
	}

	public function tableSecPattern(){
		$model = new HomeModel();
		$table = new RicvDataTable($model->initTable());
		return $table->getDatatable();
	}

	public function fullTable(){
		$model = new HomeModel();
		$table = new RicvDataTable();
		$table->setTable($model->noticeTable())
			  ->setDefaultOrder("id","DESC")
			  ->setSearch(["title","date"])
			  ->setOrder([null,null,"date"])
			  ->setOutput([$model->button(),"title","date"]);
		return $table->getDatatable();
	}
}
