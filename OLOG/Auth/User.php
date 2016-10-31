<?php

namespace OLOG\Auth;

use OLOG\Assert;

class User implements
    \OLOG\Model\InterfaceFactory,
    \OLOG\Model\InterfaceLoad,
    \OLOG\Model\InterfaceSave,
    \OLOG\Model\InterfaceDelete,
    InterfaceOwner
{
    use \OLOG\Model\FactoryTrait;
    use \OLOG\Model\ActiveRecordTrait;
    use \OLOG\Model\ProtectPropertiesTrait;

    const DB_ID = 'db_phpauth';
    const DB_TABLE_NAME = 'olog_auth_user';

    protected $created_at_ts; // initialized by constructor
    protected $login;
    protected $password_hash = "";
    protected $description;
    const _OWNER_USER_ID = 'owner_user_id';
    protected $owner_user_id;
    const _OWNER_GROUP_ID = 'owner_group_id';
    protected $owner_group_id;
    const _HAS_FULL_ACCESS = 'has_full_access';
    protected $has_full_access = 0;
    const _PRIMARY_GROUP_ID = 'primary_group_id';
    protected $primary_group_id;
    protected $id;

    public function getPrimaryGroupId()
    {
        return $this->primary_group_id;
    }

    public function setPrimaryGroupId($value)
    {
        $this->primary_group_id = $value;
    }

    public function getHasFullAccess()
    {
        return $this->has_full_access;
    }

    public function setHasFullAccess($value)
    {
        $this->has_full_access = $value;
    }

    public function __construct()
    {
        $this->created_at_ts = time();

        OwnerAssign::assignCurrentUserAsOwnerToObj($this);
    }

    public function getOwnerGroupId()
    {
        return $this->owner_group_id;
    }

    public function setOwnerGroupId($value)
    {
        $this->owner_group_id = $value;
    }

    public function getOwnerUserId()
    {
        return $this->owner_user_id;
    }

    public function setOwnerUserId($value)
    {
        $this->owner_user_id = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    public function setPasswordHash($value)
    {
        $this->password_hash = $value;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($value)
    {
        $this->login = $value;
    }

    static public function getAllIdsArrByCreatedAtDesc()
    {
        $ids_arr = \OLOG\DB\DBWrapper::readColumn(
            self::DB_ID,
            'select id from ' . self::DB_TABLE_NAME . ' order by created_at_ts desc'
        );
        return $ids_arr;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCreatedAtTs()
    {
        return $this->created_at_ts;
    }

    /**
     * @param string $title
     */
    public function setCreatedAtTs($title)
    {
        $this->created_at_ts = $title;
    }

    /**
     * @param $requested_permissions_arr
     * @return bool
     */
    public function hasAnyOfPermissions($requested_permissions_arr)
    {
        if ($this->getHasFullAccess()){
            return true;
        }

        $user_permissions_ids_arr = PermissionToUser::getIdsArrForUserIdByCreatedAtDesc($this->getId());
        foreach ($user_permissions_ids_arr as $permissiontouser_id) {
            $permissiontouser_obj = PermissionToUser::factory($permissiontouser_id);
            $permission_id = $permissiontouser_obj->getPermissionId();
            $permission_obj = Permission::factory($permission_id);
            if (in_array($permission_obj->getTitle(), $requested_permissions_arr)) {
                return true;
            }
        }

        return false;
    }

    public function afterSave()
    {
        $this->removeFromFactoryCache();
        if( AuthConfig::getUserAfterSaveCallbackClassName()){
           \OLOG\CheckClassInterfaces::exceptionIfClassNotImplementsInterface(AuthConfig::getUserAfterSaveCallbackClassName(), InterfaceUserAfterSaveCallback::class);
            $events_class = AuthConfig::getUserAfterSaveCallbackClassName();
            $events_class::userAfterSaveCallback($this);
        }
    }
}
