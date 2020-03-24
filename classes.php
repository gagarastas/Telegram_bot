<?php
class handler
{
    private $databs;
    private $chat_id;
    private $token;
    public function __construct($token)
    {
        $this->token=$token;
        $this->databs =new database();

    }
    public function set_chat_id($chat_id)
    {
        $this->chat_id=$chat_id;
    }

    public function get_update_id()
    {

        $ans=$this->databs->get_update_id();
        if(!empty($ans))
        {
            $user_id=$ans[0][0];
            return $user_id;
        }
        return false;
    }

    public function set_update_id($update_id,$key)
    {
        $ans=false;
        if($key==1)
            $ans=$this->databs->set_update_id($update_id);
        if($key==0)
            $ans=$this->databs->set_insert_id($update_id);
        return $ans;
    }

    public function text_response($text,$proxy)
    {
        $response=['chat_id'=>$this->chat_id,'text'=>$text];
        $curl=curl_init("https://api.telegram.org/bot$this->token/sendMessage");
        curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$response);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $counter=0;
        do
        {
            $ans=curl_exec($curl);
            $counter+=1;
            if($counter>3)
                break;
        }while(!empty(curl_error($curl)));
        curl_close($curl);
        return $ans;
    }
    public function save_file($proxy,$file_id,$caption)
    {
        if(!empty($user_id=$this->check_session()))
        {
            $response=['file_id'=>$file_id];
            $curl=curl_init("https://api.telegram.org/bot$this->token/getFile");
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
            curl_setopt($curl,CURLOPT_POST,1);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$response);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $chech1=0;
            do
            {
                $ans=curl_exec($curl);
                $chech1+=1;
                if($chech1>2)
                    break;
            }while(empty($ans));
            if($ans)
            {   $ans = json_decode($ans, true);
                $path= $ans['result']['file_path'];
                $tg_path="https://api.telegram.org/file/bot$this->token/$path";
                $ext =  end(explode(".", $path));
                $type=$this->check_type($path);
                $name = date("m.d.y_H.i.s").'___'.$caption.".".$ext;
                $opts = array(
                    'http'=>array(
                        'proxy'=>$proxy));
                $context = stream_context_create($opts);
                $path_on_server='/home/users/j/j1026875/incs/'.$type.'/'. $name;
                $ans=copy($tg_path, $path_on_server,$contecst);
                $state1 = $this->databs->set_file($user_id, $path_on_server);
                if($ans)
                    return"файл был успешно сохранен: $caption";
                else
                    return"ошибка сохранения: $caption";
            }
            return "ошибка сервера, повторите еще раз попытку сохранения файла: $caption";
        }
        return 'Вы должны войти или зарегистрироваться';
    }


    public function register($login,$password)
    {
        $answer=$this->databs->get_user_id_using_login($login);
        if(!empty($answer))
        {
            $user_id = $answer[0][0];
            return "user already exist";
        }
        else
        {
            $current_user_id=$this->databs->get_user_id_of_current_session($this->chat_id);
            if(!isset($current_user_id[0][0]))
            {
                $ans=$this->databs->add_user($login,$password);
                if($ans==true)
                {
                    $answer = $this->databs->get_user_id($login, $password);
                    $user_id = $answer[0][0];
                    $ns = $this->databs->set_new_session($user_id, $this->chat_id, 1);
                    $ret = "success";
                }
                else
                    $ret="unknown error";
                return $ret;
            }
            return 'Вам нужно сначала разлогинится, чтобы создать нового пользователя. Используйте команду /logout';
        }

    }
    public function logout()
    {
        $ans=$this->databs->get_user_id_of_current_session($this->chat_id);
        if(!empty($ans))
        {
            $st = $this->databs->update_session($ans[0][0],$this->chat_id,0);
            return "poka";
        }
        else
            return "unknown error";
    }
    public function login($login,$password)
    {
        $current_user_id=$this->databs->get_user_id_of_current_session($this->chat_id);
        $user_id=$this->databs->get_user_id($login,$password);
        if(isset($user_id[0][0])and !isset($current_user_id[0][0]))
        {
            $st=$this->databs->update_session($user_id[0][0],$this->chat_id,1);
            return "welcome $login";
        }
        elseif(isset($user_id[0][0])and isset($current_user_id[0][0]) and $current_user_id[0][0]!=$user_id[0][0])
        {
            return "Вы уже вошли под другим именем, используйте команду /logout чтобы разлогинится, а затем войдите как другой пользователь";
        }
        elseif(isset($user_id[0][0])and isset($current_user_id[0][0]) and $current_user_id[0][0]==$user_id[0][0])
        {
            return "Вы уже вошли как данный пользователь";
        }
        else
        {
            return 'Неправильные имя пользователя и/или пароль. Для регистрации воспользуйтесь командой /register';
        }
    }
    public function delete_user($login,$password)
    {
        $answer=$this->databs->get_user_id($login,$password);
        if(!empty($answer))
        {
            $user_id=$answer[0][0];
            $files=$this->databs->get_all_files($user_id);
            foreach($files as $file)
            {
                $path=$file['path'];
                echo $path.'<br>';
                if (file_exists($path))
                {
                    unlink($path);
                }
            }
            $ans1=$this->databs->delete_session($user_id,$this->chat_id);
            if(empty($ans1))
                return'error deleting session';
            $ans2=$this->databs->delete_all_files_of_user($user_id);
            if(empty($ans2))
                return'error deleting all files';
            $ans3=$this->databs->delete_user($login,$password);
            if(empty($ans3))
                return'error deleting user';

            return 'Пользователь удален';
        }
        else
            return "Неправильное имя пользователя и пароль";

    }
    public function check_session()
    {
        $ans=$this->databs->get_user_id_of_current_session($this->chat_id);
        if(!empty($ans))
            return $ans[0][0];
        else
            return false;
    }

    public function get_file($name,$proxy)
    {
        if(!empty($user_id=$this->check_session()))
        {
            $files=$this->databs->get_file($user_id,$name);
            if(!empty($files))
            {
                foreach ($files as $value)
                {
                    $type=$this->check_type($value['path']);
                    $name=explode('___',$value['path']);
                    if($type=='image')
                    {
                        $response=['chat_id'=>$this->chat_id,'photo'=>curl_file_create($value['path']),'caption'=>$name[1]];
                        $curl=curl_init("https://api.telegram.org/bot$this->token/sendPhoto");
                    }
                    elseif($type=='docs')
                    {
                        $response=['chat_id'=>$this->chat_id,'document'=>curl_file_create($value['path']),'caption'=>$name[1]];
                        $curl=curl_init("https://api.telegram.org/bot$this->token/sendDocument");
                    }
                    curl_setopt($curl, CURLOPT_PROXY, $proxy);
                    curl_setopt($curl,CURLOPT_POST,1);
                    curl_setopt($curl,CURLOPT_POSTFIELDS,$response);
                    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
                    curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
                    $chet=0;
                    do
                    {
                        $ans=curl_exec($curl);
                        $chet+=1;
                        if($chet>3)
                            break;
                    }while(!empty(curl_error($curl)));
                    curl_close($curl);
                }
                return 1;
            }
            $this->text_response("file does not exist",$proxy);
            return 1;
        }
        $this->text_response("you need to login",$proxy);
        return 1;
    }

    public function get_all_files($proxy,$request_type)
    {
        if(!empty($user_id=$this->check_session()))
        {
            $files = $this->databs->get_all_files($user_id);
            if (!empty($files))
            {
                $images="Ваши изображения:\n";
                $docs="Ваши документы:\n";
                foreach ($files as $value)
                {
                    $type=$this->check_type($value['path']);
                    $name=explode('___',$value['path']);
                    if($type=='image' and $request_type=='image')
                    {
                        $images.=$name[1]."\n";
                    }
                    elseif($type=='docs' and $request_type=='docs')
                    {
                        $docs.=$name[1]."\n";
                    }
                    else
                        continue;
                }
                if($request_type=='image')
                    $this->text_response($images."чтобы скачать изображение примените команду /getfile filename",$proxy);
                elseif($request_type=='docs')
                    $this->text_response($docs."чтобы скачать файл примените команду /getfile filename",$proxy);
                return 1;
            }
            else
            {
                $this->text_response("вы пока не добавили файлы",$proxy);
                return 1;
            }
        }
        $this->text_response("you need to login",$proxy);
        return 1;
    }

    public function delete_file($name)
    {
        if(!empty($user_id=$this->check_session()))
        {
            $path = $this->databs->get_file($user_id, $name);
            $state = $this->databs->delete_file($user_id, $path[0]['path']);
            if($state and !empty($path[0]['path']))
            {
                @$st = unlink($path[0]['path']);
                if ($st)
                    return 'successfully deleted';
            }
            return 'wrong name of file';
        }
        return 'you need to login';
    }

    private function check_type($path)
    {
        $image=['jpg','png','gif','bmp'];
        $docs=['txt','rtf','pdf','doc','ocx'];
        $apps=['zip','rar','exe'];
        $type=substr($path,-3);
        $type=strtolower($type);
        if(in_array($type,$image))
            return "image";
        if(in_array($type,$docs))
            return "docs";
        if(in_array($type,$apps))
            return "apps";
        return "wrong type";

    }
}




