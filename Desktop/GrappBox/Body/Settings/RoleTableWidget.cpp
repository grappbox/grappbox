#include "Settings/RoleTableWidget.h"

RoleTableWidget::RoleTableWidget(QWidget *parent) : QWidget(parent)
{
    _roles = new QMap<int, QString>();
    _users = new QMap<int, QString>();
    _newRoleWindow = new CreateNewRole();
    _colLayout = new QList<QHBoxLayout *>();
    _newUserBtn = new QPushButton(tr("Add user"));
    _newRoleBtn = new QPushButton(tr("Create role"));
    _rowLayout = new QVBoxLayout();
    _api = API::SDataManager::GetCurrentDataConnector();
    this->setLayout(_rowLayout);

    QObject::connect(_newRoleWindow, SIGNAL(RoleConfirmed()), this, SLOT(NewRoleTriggered()));
    QObject::connect(_newUserBtn, SIGNAL(released()), this, SLOT(inviteUser()));
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
                item->widget()->setParent(NULL);
            delete item;
        }
    }
    delete _colLayout;
    _colLayout = new QList<QHBoxLayout *>();
    while ((item = _rowLayout->takeAt(0)))
    {
        if (item->widget())
            item->widget()->setParent(NULL);
        delete item;
    }
    delete _rowLayout;
    _rowLayout = new QVBoxLayout(this);
    //TODO : Make API call HERE to GET informations
}

void RoleTableWidget::refresh()
{
    QMap<int, QString>::iterator rolesIT;
    QMap<int, QString>::iterator usersIT;

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

        btnDeleteUsr->setText(tr("Delete User"));
        btnDeleteUsr->SetInfo(usersIT.key());
        newLabel->setStyleSheet("font-weight: bold;");
        _colLayout->append(new QHBoxLayout());
        _rowLayout->addLayout(_colLayout->back());
        _colLayout->back()->addWidget(newLabel);
        for (rolesIT = _roles->begin(); rolesIT != _roles->end(); rolesIT++)
        {
            UserRoleCheckbox *userRoleCheckbox = new UserRoleCheckbox();


            userRoleCheckbox->SetUser(usersIT.value(), usersIT.key());
            userRoleCheckbox->SetRole(rolesIT.value(), rolesIT.key());
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
    data.append(QString(authorizations["teamTimeline"]));
    data.append(QString(authorizations["customerTimeline"]));
    data.append(QString(authorizations["gantt"]));
    data.append(QString(authorizations["whiteboard"]));
    data.append(QString(authorizations["bugtracker"]));
    data.append(QString(authorizations["event"]));
    data.append(QString(authorizations["task"]));
    data.append(QString(authorizations["projectSettings"]));
    data.append(QString(authorizations["cloud"]));

    _stackRole.append(roleName);
    _api->Post(API::DP_PROJECT, API::PR_ROLE_ADD, data, this, "SuccessAddRole", "Failure");
}

void RoleTableWidget::inviteUser()
{
    //Trigger inviteUserWindow
}

void RoleTableWidget::deleteUser(int idUser)
{

}

void RoleTableWidget::deleteRole(int idRole)
{

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
    QMessageBox::critical(this, "Connexion Error", "Failure to retreive data from internet");
    qDebug() << data;
}
