<?php

namespace fabwnklr\fat;

use fabwnklr\fat\db\DbModel;

abstract class UserModel extends DbModel
{
    abstract public function getDisplayName(): string;
}