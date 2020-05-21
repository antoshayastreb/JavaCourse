<?php
    

    interface Command
        {
            public function execute(): void;
        } 

    class Invoker
    {
       public $mode;
       
       public $stage;
       
       public $ThisStage;
       
       public $FileUploading;
       
       public $CurTheme;
       
       public $NewStage;
       
       public $currentgroup;
       
       public $ThisID;
       
       public $NewGroup;
       
       private $UDEditProfile;

       private $UDShowFullList;
       
       private $UDChgHM;
       
       private $UDShowFromList;
       
       private $UDPrevLesson;

       private $UDNextLesson;
       
       private $TDEDshow;
       
       private $TDEDadd;
       
       private $TDEDchange;
       
       private $TDEDchangeThemeConf;
       
       private $TDEDview;
       
       private $TDEGview;
       
       private $TDEDdelete;
       
       private $TDEDdelConf;
       
       private $TDSDAddStage;
       
       private $TDSDDownload;
       
       private $TDEGdelete;
       
       private $TDEGdelConf;
               
       private $TDEPshow;       

       public function setUDEditProfile(Command $command): void
       {
           $this->UDEditProfile = $command;
       }

       public function setUDShowFullList(Command $command): void
       {
           $this->UDShowFullList = $command;
       }
       
       public function setUDChgHM(Command $command):void
       {
           $this->UDChgHM = $command;
       }
       
       public function setUDShowFromList(Command $command): void
       {
           $this->UDShowFromList = $command;
       }
       
       public function setUDPrevLesson(Command $command): void
       {
           $this->UDPrevLesson = $command;
       }
       
       public function setUDNextLesson(Command $command): void
       {
           $this->UDNextLesson = $command;
       }
       
       public function setTDEDshow(Command $command): void
       {
           $this->TDEDshow = $command;
       }
       
       public function setTDEDchange(Command $command): void
       {
           $this->TDEDchange = $command;
       }
       
       public function setTDEDchangeThemeConf(Command $command): void
       {
           $this->TDEDchangeThemeConf = $command;
       }
       
       public function setTDEDadd(Command $command): void
       {
           $this->TDEDadd = $command;
       }
       
       public function setTDEDview(Command $command): void
       {
           $this->TDEDview = $command;
       }
       
       public function setTDEGview(Command $command): void
       {
           $this->TDEGview = $command;
       }
       
       public function setTDEDdelete(Command $command): void
       {
           $this->TDEDdelete = $command;
       }
       
       public function setTDEDdelConf(Command $command): void
       {
           $this->TDEDdelConf = $command;
       }
       
       public function setTDSDAddStage(Command $command): void
       {
           $this->TDSDAddStage = $command;
       }
       
       public function setTDSDDownload(Command $command): void
       {
           $this->TDSDDownload = $command;
       }
       
       public function setTDEGdelete(Command $command): void
       {
           $this->TDEGdelete = $command;
       }
       
       public function setTDEGdelConf(Command $command): void
       {
           $this->TDEGdelConf = $command;
       }
       
       public function setTDEPshow(Command $command): void
       {
           $this->TDEPshow = $command;
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
             if ($_GET['do'] == 'UDShowFromList')
                {
                    if ($this->UDShowFromList instanceof Command) 
                    {
                         $this->UDShowFromList->execute();
                    }
                } 
                
             if ($_GET['do'] == 'UDNextLesson')
                {
                    if ($this->UDNextLesson instanceof Command) 
                    {
                         $this->UDNextLesson->execute();
                    }
                }
             
             if ($_GET['do'] == 'UDPrevLesson')
                {
                    if ($this->UDPrevLesson instanceof Command) 
                    {
                         $this->UDPrevLesson->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEDshow')
                {
                    if ($this->TDEDshow instanceof Command) 
                    {
                         $this->TDEDshow->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEDchange')
                {
                    if ($this->TDEDchange instanceof Command) 
                    {
                         $this->TDEDchange->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEDchangeThemeConf')
                {
                    if ($this->TDEDchangeThemeConf instanceof Command) 
                    {
                         $this->TDEDchangeThemeConf->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEDadd')
                {
                    if ($this->TDEDadd instanceof Command) 
                    {
                         $this->TDEDadd->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEDview')
                {
                    if ($this->TDEDview instanceof Command) 
                    {
                         $this->TDEDview->execute();
                    }
                }   
                
             if ($_GET['do'] == 'TDEGview')
                {
                    if ($this->TDEGview instanceof Command) 
                    {
                         $this->TDEGview->execute();
                    }
                }       
             
             if ($_GET['do'] == 'TDEDdelete')
                {
                    if ($this->TDEDdelete instanceof Command) 
                    {
                         $this->TDEDdelete->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEDdelConf')
                {
                    if ($this->TDEDdelConf instanceof Command) 
                    {
                         $this->TDEDdelConf->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDSDAddStage')
                {
                    if ($this->TDSDAddStage instanceof Command) 
                    {
                         $this->TDSDAddStage->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDSDDownload')
                {
                    if ($this->TDSDDownload instanceof Command) 
                    {
                         $this->TDSDDownload->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEGdelete')
                {
                    if ($this->TDEGdelete instanceof Command) 
                    {
                         $this->TDEGdelete->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEGdelConf')
                {
                    if ($this->TDEGdelConf instanceof Command) 
                    {
                         $this->TDEGdelConf->execute();
                    }
                }
                
             if ($_GET['do'] == 'TDEPshow')
                {
                    if ($this->TDEPshow instanceof Command) 
                    {
                         $this->TDEPshow->execute();
                    }
                }   
           }
          
       }
    }

    
?>


