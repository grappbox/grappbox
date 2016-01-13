#include "Settings/RoleTableWidget.h"

RoleTableWidget::RoleTableWidget(QWidget *parent) : QWidget(parent)
{
    _roles = new QMap<int, QString>();
    _users = new QMap<int, QString>();
    _newRoleWindow = new CreateNewRole();
    _inviteUserWindow = new InviteUserWindow();
    _colLayout = new QList<QHBoxLayout *>();
    _newUserBtn = new QPushButton(tr("Add user"));
    _newRoleBtn = new QPushButton(tr("Create role"));
    _rowLayout = new QVBoxLayout();
    _api = API::SDataManager::GetCurrentDataConnector();
    this->setLayout(_rowLayout);

    QObject::connect(_newRoleWindow, SIGNAL(RoleConfirmed()), this, SLOT(NewRoleTriggered()));
    QObject::connect(_inviteUserWindow, SIGNAL(InviteUserCompleted(QString)), this, SLOT(InviteUser(QString)));
    QObject::connect(_newUserBtn, SIGNAL(released()), _inviteUserWindow, SLOT(Open()));
    QObject::connect(_newRoleBtn, SIGNAL(released()), _newRoleWindow, SLOT(Open()));
    this->reset();
}

RoleTableWidget::~RoleTableWidget()
{

}

void RoleTableWidget::addRole(const QString &role, const int ID)
{
    (*_roles)[ID] = QString(role);
}

void RoleTableWidget::addUser(const QString &user, const int ID)
{
    (*_users)[ID] = QString(user);
}

void RoleTableWidget::setRoles(const QMap<int, QString> &roles)
{
    QMap<int, QString>::const_iterator    it;

    for (it = roles.begin(); it != roles.end(); it++)
        addRole(it.value(), it.key());
}

void RoleTableWidget::setUsers(const QMap<int, QString> &users)
{
    QMap<int, QString>::const_iterator    it;

    for (it = users.begin(); it != users.end(); it++)
        addUser(it.value(), it.key());
}

void RoleTableWidget::Clear()
{
    _roles = new QMap<int, QString>();
    _users = new QMap<int, QString>();

}

const QMap<int, QList<int>> RoleTableWidget::getRolesByUsers()
{
    QMap<int, QList<int>> rolesByUsers;
    QList<UserRoleCheckbox *>::const_iterator it;

    for (it = _usersRolesCheckboxes->begin(); it != _usersRolesCheckboxes->end(); it++)
    {
        if (!(*it)->isChecked())
            continue;
        rolesByUsers[(*it)->getUser().second].append((*it)->getRole().second);
    }
    return rolesByUsers;
}

const QMap<int, QString> &RoleTableWidget::getRoles()
{
    return (*_roles);
}

const QMap<int, QString> &RoleTableWidget::getUsers()
{
    return (*_users);
}

void RoleTableWidget::reset()
{
    QLayoutItem *item;
    _usersRolesCheckboxes = new QList<UserRoleCheckbox *>();
    for (QHBoxLayout *lay : *_colLayout)
    {
        while ((item = lay->takeAt(0)))
        {
            if (item->widget())
                item->widget()->setParent(nullptr);
            delete item;
        }
    }
    delete _colLayout;
    _colLayout = new QList<QHBoxLayout *>();
    while ((item = _rowLayout->takeAt(0)))
    {
        if (item->widget())
            item->widget()->setParent(nullptr);
        delete item;
    }
    delete _rowLayout;
    _rowLayout = new QVBoxLayout(this);
    repaint();
}

void RoleTableWidget::refresh(bool reset)
{
    QMap<int, QString>::iterator rolesIT;
    QMap<int, QString>::iterator usersIT;

    if (reset)
        this->reset();
    //Repopulate with new data
    _colLayout->append(new QHBoxLayout());
    _rowLayout->addLayout(_colLayout->back());
    _colLayout->back()->addWidget(new QLabel(""));
    for (rolesIT = _roles->begin(); rolesIT != _roles->end(); rolesIT++)
    {
        QLabel *newLabel = new QLabel(rolesIT.value());
        newLabel->setStyleSheet("font-weight: bold;");
        _colLayout->back()->addWidget(newLabel);
        _colLayout->back()->setAlignment(newLabel, Qt::AlignCenter);
    }
    for (usersIT = _users->begin(); usersIT != _users->end(); usersIT++)
    {
        QLabel *newLabel = new QLabel(usersIT.value());
        InfoPushButton *btnDeleteUsr = new InfoPushButton();

        QObject::connect(btnDeleteUsr, SIGNAL(ReleaseInfo(int)), this, SLOT(deleteUser(int)));
        btnDeleteUsr->setText(tr("Delete User"));
        btnDeleteUsr->SetInfo(usersIT.key());
        newLabel->setStyleSheet("font-weight: bold;");
        _colLayout->append(new QHBoxLayout());
        _rowLayout->addLayout(_colLayout->back());
        _colLayout->back()->addWidget(newLabel);
        for (rolesIT = _roles->begin(); rolesIT != _roles->end(); rolesIT++)
        {
            UserRoleCheckbox *userRoleCheckbox = new UserRoleCheckbox();


            QObject::connect(userRoleCheckbox, SIGNAL(checked(UserRoleCheckbox *,QPair<const QString&,const int>,QPair<const QString&,const int>)), this, SLOT(AssignRole(UserRoleCheckbox *,QPair<const QString&,const int>,QPair<const QString&,const int>)));
            QObject::connect(userRoleCheckbox, SIGNAL(unchecked(UserRoleCheckbox *,QPair<const QString&,const int>,QPair<const QString&,const int>)), this, SLOT(DetachRole(UserRoleCheckbox *,QPair<const QString&,const int>,QPair<const QString&,const int>)));
            userRoleCheckbox->SetUser(usersIT.value(), usersIT.key());
            userRoleCheckbox->SetRole(rolesIT.value(), rolesIT.key());
            userRoleCheckbox->InitEnd(_projectId);
            _colLayout->back()->addWidget(userRoleCheckbox);
            _colLayout->back()->setAlignment(userRoleCheckbox, Qt::AlignCenter);
            _usersRolesCheckboxes->append(userRoleCheckbox);

        }
        QObject::connect(btnDeleteUsr, SIGNAL(ReleaseInfo(int)), this, SLOT(deleteUser(int)));
        _colLayout->back()->addWidget(btnDeleteUsr);
    }

    _colLayout->first()->addWidget(_newRoleBtn);
    _colLayout->append(new QHBoxLayout());
    _rowLayout->addLayout(_colLayout->back());
    _colLayout->back()->addWidget(_newUserBtn);
    for (rolesIT = _roles->begin(); rolesIT != _roles->end(); rolesIT++)
    {
        InfoPushButton *deleteRoleButton = new InfoPushButton();

        deleteRoleButton->setText(tr("Delete role"));
        deleteRoleButton->SetInfo(rolesIT.key());
        QObject::connect(deleteRoleButton, SIGNAL(ReleaseInfo(int)), this, SLOT(deleteRole(int)));
        _colLayout->back()->addWidget(deleteRoleButton);
    }
    _colLayout->back()->addWidget(new QLabel(""));
}

void RoleTableWidget::SetProjectId(int id)
{
    _projectId = id;
}

void RoleTableWidget::NewRoleTriggered()
{
    QMap<QString, bool> authorizations = _newRoleWindow->GetRoleAuthorizations();
    QString roleName = _newRoleWindow->GetRoleName();
    QVector<QString> data;
    QMap<QString, bool>::iterator it;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(roleName);
    data.append(QString::number(authorizations["teamTimeline"]));
    data.append(QString::number(authorizations["customerTimeline"]));
    data.append(QString::number(authorizations["gantt"]));
    data.append(QString::number(authorizations["whiteboard"]));
    data.append(QString::number(authorizations["bugtracker"]));
    data.append(QString::number(authorizations["event"]));
    data.append(QString::number(authorizations["task"]));
    data.append(QString::number(authorizations["projectSettings"]));
    data.append(QString::number(authorizations["cloud"]));

    _stackRole.append(roleName);
    _api->Post(API::DP_PROJECT, API::PR_ROLE_ADD, data, this, "SuccessAddRole", "FailureAddRole");
}

void RoleTableWidget::InviteUser(QString usermail) //TODO: To confirm by API
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(usermail);

    _api->Put(API::DP_PROJECT, API::PUTR_INVITE_USER, data, this, "SuccessInviteUser", "Failure");
}

void RoleTableWidget::deleteUser(int idUser)
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(QString::number(idUser));

    _stackUserDelete.append(idUser);
    _api->Delete(API::DP_PROJECT, API::DR_PROJECT_USER, data, this, "SuccessDeleteUser", "FailureDeleteUser");
}

void RoleTableWidget::deleteRole(int idRole)
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(QString::number(idRole));
    _stackRoleDelete.append(idRole);

    _api->Delete(API::DP_PROJECT, API::DR_PROJECT_ROLE, data, this, "SuccessDeleteRole", "FailureDeleteRole");
}

void RoleTableWidget::SuccessAddRole(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();

    this->addRole(_stackRole.first(), json["roleId"].toInt());
    _stackRole.pop_front();
    reset();
    refresh();
}

void RoleTableWidget::Failure(int id, QByteArray data)
{
    QMessageBox::critical(this, tr("Connexion Error"), tr("Failure to retreive data from internet"));
    qDebug() << data;
}

void RoleTableWidget::FailureAddRole(int id, QByteArray data)
{
    _stackRole.pop_front();
    Failure(id, data);
}

void RoleTableWidget::SuccessDeleteRole(int id, QByteArray data)
{
    _roles->remove(_stackRoleDelete.first());
    _stackRoleDelete.pop_front();
    reset();
    refresh();
}

void RoleTableWidget::AssignRole(UserRoleCheckbox *checkbox,const QPair<const QString &, const int> user, const QPair<const QString &, const int> role)
{
    QVector<QString> data;

    this->setEnabled(false);
    _stackRoleAssign.append(checkbox);
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(QString::number(user.second));
    data.append(QString::number(role.second));
    _api->Post(API::DP_PROJECT, API::PR_ROLE_ASSIGN, data, this, "SuccessAttachRole", "FailureAttachRole");
}

void RoleTableWidget::DetachRole(UserRoleCheckbox *checkbox,const QPair<const QString &, const int> user, const QPair<const QString &, const int> role)
{
    QVector<QString> data;

    this->setEnabled(false);
    _stackRoleDetach.append(checkbox);
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(QString::number(user.second));
    data.append(QString::number(role.second));
    _api->Delete(API::DP_PROJECT, API::DR_ROLE_DETACH, data, this, "SuccessDetachRole", "FailureDetachRole");
}

void RoleTableWidget::FailureAttachRole(int id, QByteArray data)
{
    _stackRoleAssign.first()->setChecked(false);
    _stackRoleAssign.pop_front();
    this->setEnabled(true);
    Failure(id, data);
}

void RoleTableWidget::FailureDetachRole(int id, QByteArray data)
{
    _stackRoleDetach.first()->setChecked(true);
    _stackRoleDetach.pop_front();
}

void RoleTableWidget::FailureDeleteRole(int id, QByteArray data)
{
    _stackRoleDelete.pop_front();
    this->setEnabled(true);
    Failure(id, data);
}

void RoleTableWidget::SuccessAttachRole(int id, QByteArray data)
{
    _stackRoleAssign.pop_front();
    this->setEnabled(true);
}

void RoleTableWidget::SuccessDetachRole(int id, QByteArray data)
{
    _stackRoleDetach.pop_front();
    this->setEnabled(true);
}

void RoleTableWidget::SuccessInviteUser(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();

    _users->insert(json["user_id"].toInt(), QString(json["user_firstname"].toString() + " " + json["user_lastname"].toString()));
    refresh(true);
}

void RoleTableWidget::SuccessDeleteUser(int id, QByteArray data)
{
    _users->remove(_stackUserDelete.first());
    _stackUserDelete.pop_front();
   refresh(true);
}

void RoleTableWidget::FailureDeleteUser(int id, QByteArray data)
{
    _stackUserDelete.pop_front();
    Failure(id, data);
}
