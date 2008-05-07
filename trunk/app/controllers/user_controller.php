<?php
class UserController extends AppController
{
	/*
	 * This should load the user data, as well as common admin variables. currently working on what those are
	 * 
	 */
	var $layout = "simple";
	var $uses = array('User'); 
	
    function login()
    {
        $this->set('error', false);
        if (!empty($this->data))
        {
            $someone = $this->User->findByUsername($this->data['User']['username']);
            if(!empty($someone['User']['password']) && $someone['User']['password'] == $this->data['User']['password'])
            {
                $this->Session->write('User', $someone['User']);
				$this->Session->write('id', $someone['User']['id']);
                $this->redirect('/admin');
            }
            else
            {
                // Remember the $error var in the view? Let's set that to true:
                $this->set('error', true);
            }
        }
    }
	
	function ajaxnologin(){
		$status = array("status"=>false,"message"=>"Not logged in, cannot use admin functions");
		$this->set('output',$status);
		$this->render('json','ajax');
	}
    
    function home(){
    	//Reload user profile when reloading the home tab
		$this->checkSession();
		if($this->Session->check('id')){	
			$uid = $this->Session->read('id');
			$this->User->id = $uid;
			$user = $this->User->read();		
			$this->set('user_profile',$user['User']['profile']);
		}else{
			//TODO set default profile?
			$this->set('user_profile','{"default":"not logged in"}');
		}		
		$this->set('output','NOTHING TO SEE HERE!');
    	$this->render('home','ajax');
    }
	
	/*
	 * Save the json string in the profile after a widget config update
	 */
	function saveconfig(){
		$this->checkSession();
		$status['status'] = false;
		if($this->Session->check('id')){	
			$uid = $this->Session->read('id');
			$this->User->id = $uid;
			$user['User']['profile'] = file_get_contents('php://input');
			//$this->User->profile = file_get_contents('php://input');
			$user = $this->User->save($user);		
			//$this->set('user_profile',$user['User']['profile']);
			$status['status'] = true;
			$status['message'] = "User Profile Saved";
		}else{
			//TODO set default profile?
			$this->set('user_profile','{"default":"not logged in"}');
			$status['message'] = "are you logged in?";
		}
		$status['status'] = true;
		$this->set('output',$status);
    	$this->render('json','ajax');		
	}

    function logout()
    {
		$this->Session->delete('User');
        $this->redirect('/');
    }
    
    function checkLogin(){
    	//Check that the user is logged in and they are an admin or not
    	$sessinfo = array();
    	if($this->Session->check('User')){
    		//the user is logged in, allow the wiki edit featur
    		//the layout will know more than we do now 
    		$cuser = $this->Session->read('User');
    		if($cuser['account_type'] == 'admin'){
    			$sessinfo['admin_enable'] = true;
    		}else{
    			$sessinfo['admin_enable'] = false;
    		}
    		
    	}else{
    		$sessinfo['admin_enable'] = false;
    	}
    	if(isset($this->params['requested'])) {
    		return $sessinfo;
        }else{
        	//not currently planned for usage at this point, but could be
        	$this->set('sessinfo', $sessinfo);
        }
    }//end checkLogin

	function edit(){
		$this->layout = 'ajax';
		$this->set('mysession',$this->Session->read('User'));
	}
	
	function getUserID(){
		if($this->Session->check('User')){
			$cuser = $this->Session->read('User');
			if($cuser['id'] != null){
				return $cuser['id'];
			}else{
				return false;
			}
		}else{
			return false;
		}	
	}			
}
?>