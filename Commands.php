<?php
    

    interface Command
        {
            public function execute(): void;
        } 

    class Invoker
    {
       public $mode;
        
       private $UDEditProfile;

       private $UDShowFullList;
       
       private $UDChgHM;

       public function setUDEditProfile(Command $command): void
       {
           $this->UDEditProfile = $command;
       }

       public function setUDShowFullList(Command $command): void
       {
           $this->UDShowFullList = $command;
       }
       
       public function setUDChgHM (Command $command):void
       {
           $this->UDChgHM = $command;
       }
       
       public function comExect()
       {
           if (count($_GET)>0)
           {
             if ($_GET['do'] == 'UDEditProfile')
                {
                    if ($this->UDEditProfile instanceof Command) 
                    {
                         $this->UDEditProfile->execute();
                    }
                }
             if ($_GET['do'] == 'UDShowFullList')
                {
                    if ($this->UDShowFullList instanceof Command) 
                    {
                         $this->UDShowFullList->execute();
                    }
                }
             if (($_GET['do'] == 'UDDelHM')|($_GET['do'] == 'UDChgHM'))
                {
                    if ($this->UDChgHM instanceof Command) 
                    {
                         $this->UDChgHM->execute();
                    }
                }
           }
          
       }
    }

    
?>
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

