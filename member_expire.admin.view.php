<?php

/**
 * 휴면계정 정리 모듈
 * 
 * Copyright (c) 2015, Kijin Sung <kijin@kijinsung.com>
 * 
 * 이 프로그램은 자유 소프트웨어입니다. 소프트웨어의 피양도자는 자유 소프트웨어
 * 재단이 공표한 GNU 일반 공중 사용 허가서 2판 또는 그 이후 판을 임의로
 * 선택해서, 그 규정에 따라 프로그램을 개작하거나 재배포할 수 있습니다.
 *
 * 이 프로그램은 유용하게 사용될 수 있으리라는 희망에서 배포되고 있지만,
 * 특정한 목적에 맞는 적합성 여부나 판매용으로 사용할 수 있으리라는
 * 묵시적인 보증을 포함한 어떠한 형태의 보증도 제공하지 않습니다.
 * 보다 자세한 사항에 대해서는 GNU 일반 공중 사용 허가서를 참고하시기 바랍니다.
 *
 * GNU 일반 공중 사용 허가서는 이 프로그램과 함께 제공됩니다.
 * 만약, 이 문서가 누락되어 있다면 자유 소프트웨어 재단으로 문의하시기 바랍니다.
 */
class Member_ExpireAdminView extends Member_Expire
{
	/**
	 * 모듈 설정 화면을 표시하는 메소드.
	 */
	public function dispMember_ExpireAdminConfig()
	{
		// 현재 설정을 불러온다.
		Context::set('mex_config', $this->getConfig());
		
		// 템플릿을 지정한다.
		$this->setTemplatePath($this->module_path.'tpl');
		$this->setTemplateFile('config');
	}
	
	/**
	 * 휴면 계정 정리 화면을 표시하는 메소드.
	 */
	public function dispMember_ExpireAdminCleanup()
	{
		// 현재 설정을 불러온다.
		$config = $this->getConfig();
		Context::set('mex_config', $this->getConfig());
		
		// 휴면계정 수를 불러온다.
		$obj = new stdClass();
		$obj->is_admin = 'N';
		$obj->threshold = date('YmdHis', time() - ($config->expire_threshold * 86400) + zgap());
		$obj->page = $page = Context::get('page') ?: 1;
		$expired_members_count = executeQuery('member_expire.countExpiredMembers', $obj);
		$expired_members_count = $expired_members_count->toBool() ? $expired_members_count->data->count : 0;
		Context::set('expire_threshold', $this->translateThreshold($config->expire_threshold));
		Context::set('expired_members_count', $expired_members_count);
		
		// 템플릿을 지정한다.
		$this->setTemplatePath($this->module_path.'tpl');
		$this->setTemplateFile('cleanup');
	}
	
	/**
	 * 정리대상 회원 목록을 표시하는 메소드.
	 */
	public function dispMember_ExpireAdminListTargets()
	{
		// 현재 설정을 불러온다.
		$config = $this->getConfig();
		Context::set('mex_config', $config);
		
		// 휴면계정 목록을 불러온다.
		$obj = new stdClass();
		$obj->is_admin = 'N';
		$obj->threshold = date('YmdHis', time() - ($config->expire_threshold * 86400) + zgap());
		$obj->page = $page = Context::get('page') ?: 1;
		$obj->orderby = 'desc';
		$expired_members_count = executeQuery('member_expire.countExpiredMembers', $obj);
		$expired_members_count = $expired_members_count->toBool() ? $expired_members_count->data->count : 0;
		$expired_members = executeQuery('member_expire.getExpiredMembers', $obj);
		$expired_members = $expired_members->toBool() ? $expired_members->data : array();
		Context::set('expire_threshold', $this->translateThreshold($config->expire_threshold));
		Context::set('expired_members_count', $expired_members_count);
		Context::set('expired_members', $expired_members);
		
		// 페이징을 처리한다.
		$paging = new Object();
		$paging->total_count = $expired_members_count;
		$paging->total_page = max(1, ceil($expired_members_count / 10));
		$paging->page = $page;
		$paging->page_navigation = new PageHandler($paging->total_count, $paging->total_page, $page, 10);
		Context::set('paging', $paging);
		Context::set('page', $page);
		
		// 템플릿을 지정한다.
		$this->setTemplatePath($this->module_path.'tpl');
		$this->setTemplateFile('list_targets');
	}
	
	/**
	 * 별도저장 회원 목록을 표시하는 메소드.
	 */
	public function dispMember_ExpireAdminListMoved()
	{
		// 현재 설정을 불러온다.
		$config = $this->getConfig();
		Context::set('mex_config', $config);
		
		// 휴면계정 목록을 불러온다.
		$obj = new stdClass();
		$obj->page = $page = Context::get('page') ?: 1;
		$obj->orderby = 'desc';
		$moved_members_count = executeQuery('member_expire.countMovedMembers', $obj);
		$moved_members_count = $moved_members_count->toBool() ? $moved_members_count->data->count : 0;
		$moved_members = executeQuery('member_expire.getMovedMembers', $obj);
		$moved_members = $moved_members->toBool() ? $moved_members->data : array();
		Context::set('expire_threshold', $this->translateThreshold($config->expire_threshold));
		Context::set('moved_members_count', $moved_members_count);
		Context::set('moved_members', $moved_members);
		
		// 페이징을 처리한다.
		$paging = new Object();
		$paging->total_count = $moved_members_count;
		$paging->total_page = max(1, ceil($moved_members_count / 10));
		$paging->page = $page;
		$paging->page_navigation = new PageHandler($paging->total_count, $paging->total_page, $page, 10);
		Context::set('paging', $paging);
		Context::set('page', $page);
		
		// 템플릿을 지정한다.
		$this->setTemplatePath($this->module_path.'tpl');
		$this->setTemplateFile('list_moved');
	}
	
	/**
	 * 숫자로 지정된 기간을 사람이 이해하기 쉬운 표현으로 변경하는 메소드.
	 */
	protected function translateThreshold($days)
	{
		if ($days < 360)
		{
			return round($days / 30.25) . '개월';
		}
		else
		{
			return round($days / 365) . '년';
		}
	}
}
