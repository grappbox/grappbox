#include "UserRoleCheckbox.h"

UserRoleCheckbox::UserRoleCheckbox(QWidget *parent) : QCheckBox(parent)
{
    QObject::connect(this, SIGNAL(toggled(bool)), this, SLOT(checkChange(bool)));
}

void UserRoleCheckbox::SetUser(const QString &username, const int ID)
{
    _userName = username;
    _userID = ID;
}

void UserRoleCheckbox::SetRole(const QString &roleName, const int ID)
{
    _roleName = roleName;
    _roleID = ID;
}

const QPair<const QString &, const int> UserRoleCheckbox::getUser()
{
    return QPair<const QString &, const int>(_userName, _userID);
}

const QPair<const QString &, const int> UserRoleCheckbox::getRole()
{
    return QPair<const QString &, const int>(_roleName, _roleID);
}

void UserRoleCheckbox::checkChange(bool state)
{
    if (state)
        emit checked(this, QPair<QString, int>(_userName, _userID), QPair<QString, int>(_roleName, _roleID));
    else
        emit unchecked(this, QPair<QString, int>(_userName, _userID), QPair<QString, int>(_roleName, _roleID));
}
