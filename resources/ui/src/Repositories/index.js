import AuthorsRepository from './authorsRepository';
import CommentRepository from './commentRepository';
import ThreadsRepository from './threadsRepository';

class Repositories {

}

Repositories.AuthorsRepository = AuthorsRepository;
Repositories.ThreadsRepository = ThreadsRepository;
Repositories.CommentRepository = CommentRepository;

export {
  Repositories, AuthorsRepository, ThreadsRepository, CommentRepository
};
