<?php

namespace Sandbox;

use Sandbox\Templating\Helpers;
use Sandbox\Controls\Form;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
	public function createForm()
	{
		$form = new Form();
		return $form;
	}

	public function beforeRender()
	{
		parent::beforeRender();
		Helpers::prepareTemplate($this->template);
	}

	public function getParamByName($name)
	{
		return $this->params[$name];
	}

	public function handleLogout()
	{
		if($this->user->isLoggedIn()) {
			$this->user->logout(TRUE);
			$this->flashSuccess('Byl jsi odhlášen.');
		}
		$this->redirect(":Public:Dashboard:default");
	}

	public function flashError($message)
	{
		return $this->flashMessage($message, 'danger');
	}

	public function flashSuccess($message)
	{
		return $this->flashMessage($message, 'success');
	}

	public function refresh() {
		$this->redirect('this');
	}

	public function createComponentPaginatorForm()
	{
		$form = new \Nette\Application\UI\Form();
		$form->addText('page', 'Přejít na stranu:')
			->setRequired('Prosím vyplň stranu na kterou chceš přejít.')
			->setAttribute('placeholder', 'Stránka');

		$form->addHidden('maxPage');
		$form->addSubmit('goto', 'Přejít');
		$form->onSuccess[] = $this->paginatorFormSuccess;
		return $form;
	}

	public function paginatorFormSuccess(Form $form)
	{
		$v = $form->getValues();
		if($v->maxPage >= $v->page) {
			$this->redirect('this', array('page' => $v->page));
		} else {
			$this->redirect('this', array('page' => $v->maxPage));
		}
	}
}
