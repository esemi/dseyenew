<?php

/*
 * выводит список миров сбоку от контента(для основных страниц)
 */
class Zend_View_Helper_WorldList extends Zend_View_Helper_Abstract
{

    public function WorldList()
    {
       if( !isset($this->view->listWorlds) )
               return false;
       
       echo '<div class="world-list float-left w-123 mrg-left-21 font-17"><ul>';

       foreach($this->view->listWorlds as $world)
       {
           //$status = ($world->status == 'delete') ? '(удалён)' : '';
           echo "<li>
                   <a href='{$this->view->url(array( 'idW' => $world->id ), 'worldIndex', true)}' class='no-underline main-color-link voran-hover'>
                   {$world->name}
                   </a>
                </li>";
       }
       
       echo '</ul></div>';
    }


}
