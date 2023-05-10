artvys/cake-search
=============

This package is [CakePHP](https://cakephp.org) adapter for the package
[artvys/search](https://github.com/dtokos/php-search). It provides few boilerplate classes to make integration with
framework breeze.

# 1. Installation

This section describes installation of this package. If you have [composer](https://getcomposer.org) installed,
then simply run:

```shell
composer require artvys/cake-search
```

You **don't** need to install the core package separately. It will be installed automatically.

# 2. Implementing your [TableSearchSource](src/Engines/Compiled/SearchSources/Table/TableSearchSource.php)

This package provides you base class [TableSearchSource](src/Engines/Compiled/SearchSources/Table/TableSearchSource.php)
that includes boilerplate code for searching in [Table](https://book.cakephp.org/4/en/orm/table-objects.html) classes
from [CakePHP](https://cakephp.org).

To use it, you need to extend it and implement 3 abstract methods:

```php
use App\Model\Entity\Article;
use Artvys\Search\Cake\Engines\Compiled\SearchSources\Table\TableSearchSource;

class ArticlesTableSearchSource extends TableSearchSource {
    protected function table(): Table {
        return TableRegistry::getTableLocator()->get('Articles');
    }

    protected function fields(SearchFieldBuilder $builder, CompiledQuery $query, int $limit): void {
        $builder->add(Field::contains('title'))
            ->add(Field::contains('body'));
    }

    protected function makeResultMapper(): callable {
        return fn(Article $a) => SearchResult::make($a->title, $a->body, Router::url(['controller' => 'Articles', 'action' => 'edit']));
    }
}
```

Those methods do 3 simple things. You need to specify which `Table` will be searched, which columns will be used and how
to convert instances of entities to `SearchResult`. Please note that you don't need to use `TableRegistry` or `Router`
directly. You can inject your dependencies and then just return them in those required methods.

Take look through the base classes. They contain a lot of little methods meant for extension and can prove to be quite
handy for a lot of common use cases.

# 3. Where to go from here?

The rest of the documentation can be found in the core package [artvys/search](https://github.com/dtokos/php-search).
