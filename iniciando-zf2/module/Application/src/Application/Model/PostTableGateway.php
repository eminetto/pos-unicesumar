<?php
namespace Application\Model;

 use Zend\Db\TableGateway\TableGateway;

 class PostTableGateway
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }

     public function fetchAll()
     {
         $resultSet = $this->tableGateway->select();
         return $resultSet;
     }

     public function get($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("NÃ£o encontrado id $id");
         }
         return $row;
     }

     public function save(Post $post)
     {
         $data = array(
             'title'  => $post->title,
             'description'  => $post->description,
             'post_date'  => $post->post_date,
         );

         $id = (int) $post->id;
         if ($id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->get($id)) {
                 $this->tableGateway->update($data, array('id' => $id));
             } else {
                 throw new \Exception('Post nÃ£o existe');
             }
         }
     }

     public function delete($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }
 }