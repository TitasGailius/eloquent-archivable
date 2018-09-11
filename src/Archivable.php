<?php

namespace TitasGailius\EloquentArchivable;

use Illuminate\Database\Eloquent\Model;

trait Archivable
{
    /**
     * Boot the archivable trait for a model.
     *
     * @return void
     */
    public static function bootArchivable()
    {
        static::addGlobalScope(new ArchiveScope);

        static::$dispatcher->listen('eloquent.booted: '.static::class, function (Model $model) {
            $model->addObservableEvents(['archiving', 'archived', 'unarchiving', 'unarchived']);
        });
    }

    /**
     * Archived model instance.
     *
     * @return bool|null
     */
    public function archive()
    {
        if ($this->fireModelEvent('archiving') === false) {
            return false;
        }

        $this->{$this->getArchivedAtColumn()} = $this->freshTimestampString();

        return tap($this->save(), function () {
            $this->fireModelEvent('archived', false);
        });
    }

    /**
     * Unarchive an archived model instance.
     *
     * @return bool|null
     */
    public function unarchive()
    {
        if ($this->fireModelEvent('unarchiving') === false) {
            return false;
        }

        $this->{$this->getArchivedAtColumn()} = null;

        return tap($this->save(), function () {
            $this->fireModelEvent('unarchived', false);
        });
    }

    /**
     * Get the name of the "archived at" column.
     *
     * @return string
     */
    public function getArchivedAtColumn(): string
    {
        return defined('static::ARCHIVED_AT') ? static::ARCHIVED_AT : 'archived_at';
    }

    /**
     * Get the fully qualified "archived at" column.
     *
     * @return string
     */
    public function getQualifiedArchivedAtColumn()
    {
        return $this->qualifyColumn($this->getArchivedAtColumn());
    }

    /**
     * Register an archiving model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function archiving($callback)
    {
        static::registerModelEvent('archiving', $callback);
    }

    /**
     * Register an archived model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function archived($callback)
    {
        static::registerModelEvent('archived', $callback);
    }

    /**
     * Register an unarchiving model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function unarchiving($callback)
    {
        static::registerModelEvent('unarchiving', $callback);
    }

    /**
     * Register an unarchived model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function unarchived($callback)
    {
        static::registerModelEvent('unarchived', $callback);
    }
}
