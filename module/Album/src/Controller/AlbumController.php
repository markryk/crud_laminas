<?php
    namespace Album\Controller;

    use Album\Form\AlbumForm;
    use Album\Model\Album;

    use Album\Model\AlbumTable;
    use Laminas\Mvc\Controller\AbstractActionController;
    use Laminas\View\Model\ViewModel;
    
    class AlbumController extends AbstractActionController
    {
        private $table;

        // Add this constructor:
        public function __construct(AlbumTable $table) {
            $this->table = $table;
        }
    
        //Controller de mostrar todos os álbuns
        public function indexAction() {
            return new ViewModel([
                'albums' => $this->table->fetchAll(),
            ]);
        }

        public function showAction() {
            $id = (int) $this->params()->fromRoute('id', 0);

            if (0 === $id) {
                return $this->redirect()->toRoute('album', ['action' => 'add']);
            }

            // Retrieve the album with the specified id.
            // Doing so raises an exception if the album is not found,
            // which should result in redirecting to the landing page.
            try {
                //Método getAlbum está no arq. AlbumTable.php
                $album = $this->table->getAlbum($id);
            } catch (\Exception $e) {
                return $this->redirect()->toRoute('album', ['action' => 'index']);
            }

            //Carrega o formulário (sem o botão de submit)
            //Instancia um objeto da classe AlbumForm (arq. AlbumForm.php)
            $form = new AlbumForm();
            
            $form->bind($album);

            //Configura o botão de submit do form (no caso, adicionar)
            //$form->get('submit')->setAttribute('value', 'Edit album');
            //$form->get('submit')->setValue('Edit album'); //Será se esse comando também dá certo?

            $request = $this->getRequest();
            $viewData = ['id' => $id, 'form' => $form];

            if (! $request->isPost()) {
                return $viewData;
            }

            //Valida o formulário
            //$form->setInputFilter($album->getInputFilter());
            
            $form->setData($request->getPost());

            if (! $form->isValid()) {
                return $viewData;
            }

            try {
                //Método saveAlbum está no arq. AlbumTable.php
                $this->table->saveAlbum($album);
            } catch (\Exception $e) {
            }

            //Redireciona p/ o index
            return $this->redirect()->toRoute('album', ['action' => 'index']);
        }
    
        //Controller de adicionar álbum
        public function addAction() {

            //Carrega o formulário (sem o botão de submit)
            $form = new AlbumForm();

            //Configura o botão de submit do form (no caso, adicionar)
            $form->get('submit')->setValue('Add album');

            $request = $this->getRequest();

            if (! $request->isPost()) {
                return ['form' => $form];
            }

            //Inicializa a classe Álbum para uma variável
            $album = new Album();

            //Valida o formulário
            //Métodos setInputFilter() e getInputFilter() estão em Album.php
            $form->setInputFilter($album->getInputFilter());

            $form->setData($request->getPost());

            if (! $form->isValid()) {
                return ['form' => $form];
            }

            //Põe os dados do form no objeto $album
            //Método exchangeArray está em Album.php
            $album->exchangeArray($form->getData());

            //Põe os dados do objeto $album na tabela
            //Método saveAlbum está em AlbumTable.php
            $this->table->saveAlbum($album);

            //Redireciona p/ o index
            return $this->redirect()->toRoute('album');
        }
    
        //Controller de editar álbum
        public function editAction() {
            $id = (int) $this->params()->fromRoute('id', 0);

            if (0 === $id) {
                return $this->redirect()->toRoute('album', ['action' => 'add']);
            }

            // Retrieve the album with the specified id.
            // Doing so raises an exception if the album is not found,
            // which should result in redirecting to the landing page.
            try {
                //Método getAlbum está em AlbumTable.php
                $album = $this->table->getAlbum($id);
            } catch (\Exception $e) {
                //Se não achar o álbum, retorna para o index
                return $this->redirect()->toRoute('album', ['action' => 'index']);
            }

            //Carrega o formulário (sem o botão de submit)
            //Instancia um objeto da classe AlbumForm (arq. AlbumForm.php)
            $form = new AlbumForm();
            
            $form->bind($album);

            //Configura o botão de submit do form (no caso, editar)
            $form->get('submit')->setAttribute('value', 'Edit album');
            //$form->get('submit')->setValue('Edit album'); //Método setValue() também dá certo

            $request = $this->getRequest();
            $viewData = ['id' => $id, 'form' => $form];

            if (! $request->isPost()) {
                return $viewData;
            }

            //Valida o formulário
            //Métodos setInputFilter() e getInputFilter() estão em Album.php
            $form->setInputFilter($album->getInputFilter());
            
            $form->setData($request->getPost());

            if (! $form->isValid()) {
                return $viewData;
            }

            try {
                //Põe os dados do objeto $album na tabela
                //Método saveAlbum está em AlbumTable.php
                $this->table->saveAlbum($album);
            } catch (\Exception $e) {
            }

            //Redireciona p/ o index
            return $this->redirect()->toRoute('album', ['action' => 'index']);
        }
    
        //Controller de excluir álbum
        public function deleteAction() {
            $id = (int) $this->params()->fromRoute('id', 0);

            if (!$id) {
                //Se não encontrar o id correspondente, retorna para o index
                return $this->redirect()->toRoute('album');
            }

            $request = $this->getRequest();
            if ($request->isPost()) {
                $del = $request->getPost('del', 'No');

                if ($del == 'Yes') {
                    $id = (int) $request->getPost('id');

                    //Método deleteAlbum está em AlbumTable.php
                    $this->table->deleteAlbum($id);
                }

                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }

            return [
                'id'    => $id,
                'album' => $this->table->getAlbum($id),
            ];
        }
    }
?>