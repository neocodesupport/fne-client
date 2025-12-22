<?php

use Neocode\FNE\Cache\ArrayCache;

beforeEach(function () {
    $this->cache = new ArrayCache();
});

test('ArrayCache can store and retrieve values', function () {
    $this->cache->set('key1', 'value1');

    expect($this->cache->get('key1'))->toBe('value1');
    expect($this->cache->has('key1'))->toBeTrue();
});

test('ArrayCache returns default value when key does not exist', function () {
    expect($this->cache->get('nonexistent', 'default'))->toBe('default');
    expect($this->cache->has('nonexistent'))->toBeFalse();
});

test('ArrayCache can delete values', function () {
    $this->cache->set('key1', 'value1');
    expect($this->cache->has('key1'))->toBeTrue();

    $this->cache->delete('key1');
    expect($this->cache->has('key1'))->toBeFalse();
});

test('ArrayCache can clear all values', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');

    $this->cache->clear();

    expect($this->cache->has('key1'))->toBeFalse();
    expect($this->cache->has('key2'))->toBeFalse();
});

test('ArrayCache respects TTL expiration', function () {
    $this->cache->set('key1', 'value1', 1); // 1 second TTL

    expect($this->cache->has('key1'))->toBeTrue();
    expect($this->cache->get('key1'))->toBe('value1');

    // Wait for expiration
    sleep(2);

    expect($this->cache->has('key1'))->toBeFalse();
    expect($this->cache->get('key1'))->toBeNull();
});

test('ArrayCache can handle multiple values', function () {
    $this->cache->setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
    ]);

    $values = $this->cache->getMultiple(['key1', 'key2']);

    expect($values['key1'])->toBe('value1');
    expect($values['key2'])->toBe('value2');
});

test('ArrayCache can delete multiple keys', function () {
    $this->cache->set('key1', 'value1');
    $this->cache->set('key2', 'value2');
    $this->cache->set('key3', 'value3');

    $this->cache->deleteMultiple(['key1', 'key2']);

    expect($this->cache->has('key1'))->toBeFalse();
    expect($this->cache->has('key2'))->toBeFalse();
    expect($this->cache->has('key3'))->toBeTrue();
});

