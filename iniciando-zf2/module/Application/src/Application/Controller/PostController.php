<?php
namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Form\Post as PostForm;
use Application\Model\Post as PostModel;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect as PaginatorDbSelectAdapter;
use Zend\Db\Sql\Sql;

/**
 * Controlador que gerencia os posts
 * 
 * @category Application
 * @package Controller
 * @author  Elton Minetto <eminetto@coderockr.com>
 */
class PostController extends AbstractActionController
{
    private $tableGateway;

    private function getTableGateway()
    {
        if (!$this->tableGateway) {
            $this->tableGateway = $this->getServiceLocator()
                                       ->get('Application\Model\PostTableGateway');                                       
        }
        return $this->tableGateway;
    }

    /**
    * Mostra os posts cadastrados
    * @return void
    */
    public function indexAction()
    {
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($adapter);
        $select = $sql->select()->from('posts');
        $paginatorAdapter = new PaginatorDbSelectAdapter($select, $sql);
        $paginator = new Paginator($paginatorAdapter);
        $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
        $paginator->setItemCountPerPage($this->params()->fromRoute('itens',50));
        
        $cache = $this->getServiceLocator()->get('Cache');
        $paginator->setCache($cache);

        return new ViewModel(array(
            'posts' => $paginator
        ));

    }

    /**
    * Cria ou edita um post
    * @return void
    */
    public function saveAction()
    {
        $translator = $this->getServiceLocator()->get('translator');
        $cache = $this->getServiceLocator()->get('Cache');
        $translator->setCache($cache);

        \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);

        $form = new PostForm();
        $tableGateway = $this->getTableGateway();
        $request = $this->getRequest();
        /* se a requisiÃ§Ã£o Ã© post os dados foram enviados via formulÃ¡rio*/
        if ($request->isPost()) {
            $post = new PostModel;
            /* configura a validaÃ§Ã£o do formulÃ¡rio com os filtros
             e validators da entidade*/
            $form->setInputFilter($post->getInputFilter());
            /* preenche o formulÃ¡rio com os dados que o usuÃ¡rio digitou na tela*/
            $form->setData($request->getPost());
            /* faz a validaÃ§Ã£o do formulÃ¡rio*/
            if ($form->isValid()) {
                /* pega os dados validados e filtrados */
                $data = $form->getData();
                /* armazena a data de inclusÃ£o do post*/
                $data['post_date'] = date('Y-m-d H:i:s');
                /* preenche os dados do objeto Post com os dados do formulÃ¡rio*/
                $post->exchangeArray($data);
                /* salva o novo post*/
                $tableGateway->save($post);
                /* redireciona para a pÃ¡gina inicial que mostra todos os posts*/
                return $this->redirect()->toUrl('/post');
            }
        }
        /* essa Ã© a forma de recuperar um parÃ¢metro vindo da url como:
            http://iniciando-zf2.dev/post/save/1
        */
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id > 0) { //Ã© uma atualizaÃ§Ã£o   
            /* busca a entidade no banco de dados*/
            $post = $tableGateway->get($id);
            /* preenche o formulÃ¡rio com os  dados do banco de dados*/
            $form->bind($post);
            // $form->getElement('titulo')->setValue($post->title);
            /* muda o texto do botÃ£o submit*/
            $form->get('submit')->setAttribute('value', 'Edit');
        }
        return new ViewModel(
            array('form' => $form)
        );
    }

    /**
    * Exclui um post
    * @return void
    */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id == 0) {
            throw new \Exception("CÃ³digo obrigatÃ³rio");
        }
        /* remove o registro e redireciona para a pÃ¡gina inicial*/
        $tableGateway = $this->getTableGateway()->delete($id);
        
        return $this->redirect()->toUrl('/post');
    }

}
