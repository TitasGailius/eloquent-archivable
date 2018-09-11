# Archivable Eloquent models

This package allow you to archive Eloquent models.

# Installation

```
composer require titasgailius/eloquent-archivable
```

Next, add `Titasgailius\EloquentArchivable\Archivable` trait to any of your eloquent model.
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Titasgailius\EloquentArchivable\Archivable;

class Post extends Model
{
    use Archivable;
}
```

**Setup a database column to store an archivation date.**
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->dateTime('archived_at')->nullable();
            $table->timestamps();
        });
    }
}
```
By default, this package looks for an `archived_at` column but you may easily [change it.](#customization)

# Usage

**Archived models will be excluded automatically.**

```php
$posts = Post::all(); // Posts that are not archived.
```

**Including archived models**
```php
$posts = Post::withArchived()->all();
```

**Retrieving archived models**
```php
$posts = Post::onlyArchived()->all();

```
**Updating many records**
```php
Post::whereDate('last_comment', '<', '2018-01-01')->archive();
Post::where('comment_count', '>', 100)->unarchive();
```
Note: When updating many records Eloquent events will not be fired.

## Models

You may `archive` and `unarchive` your Eloquent models.
```php
$post->archive();
$post->unarchive();
```

## Events

Eloquent models that can be archived fire several events, allowing you to hook into the following points in a model's lifecycle:
`archiving`, `archived`, `unarchiving`, `unarchived`.
Events allow you to easily execute code each time a specific model class is archived or unarchived.
To get started, define a `$dispatchesEvents` property on your Eloquent model that maps various points of the Eloquent model's lifecycle to your own event classes:
```php
<?php

namespace App;

use App\Events\PostArchived;
use App\Events\PostArchiving;
use Illuminate\Database\Eloquent\Model;
use Titasgailius\EloquentArchivable\Archivable;

class Post extends Model
{
    use Archivable;

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'archiving' => PostArchiving::class,
        'archived' => PostArchived::class,
    ];
}
```

## Model observers

You may also use observers to group all of your listeners into a single class.
```php
<?php

namespace App\Observers;

use App\Post;

class PostObserver
{
    /**
     * Handle to the Post "archived" event.
     *
     * @param  \App\Post  $user
     * @return void
     */
    public function archived(Post $post)
    {
        //
    }

    /**
     * Handle to the Post "unarchived" event.
     *
     * @param  \App\Post  $user
     * @return void
     */
    public function unarchived(Post $post)
    {
        //
    }
}
```

To register an observer, use the `observe` method on the model you wish to observe.
You may register observers in the boot method of one of your service providers.
In this example, we'll register the observer in the `AppServiceProvider`:
```php
<?php

namespace App\Providers;

use App\Post;
use App\Observers\PostObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Post::observe(PostObserver::class);
    }
}
```

# Customization
You may change the column name that is used to store archivation date by specifying `ARCHIVED_AT` constant.
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Titasgailius\EloquentArchivable\Archivable;

class Post extends Model
{
    use Archivable;

    const ARCHIVED_AT = 'archivation_date';
}
```
