<?php

namespace tkueue\attribute;

use Attribute;

/**
 * 权限注解
 * 
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Permission
{
    private $tags;

    /**
     * 初始化
     *
     * @param array ...$tags
     */
    public function __construct(...$tags)
    {
        $this->tags = array_unique($tags);
    }

    /**
     * 判定权限。
     *
     * @param array $tags
     * @return bool
     */
    public function permit($tags)
    {
        $intersect = array_intersect($this->tags, $tags);
        return count($intersect) > 0;
    }
}
