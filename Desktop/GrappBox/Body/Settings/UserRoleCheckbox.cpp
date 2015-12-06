#include "UserRoleCheckbox.h"

UserRoleCheckbox::UserRoleCheckbox(QWidget *parent) : QCheckBox(parent)
{
    _api = API::SDataManager::GetCurrentDataConnector();
}

void UserRoleCheckbox::InitEnd(int projectId)
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(projectId));
    data.append(QString::number(_userID));
    _api->Get(API::DP_PROJECT, API::GR_PROJECT_USER_ROLE, data, this, "SuccessInit", "Failure");
}

void UserRoleCheckbox::SuccessInit(int id, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject json = doc.object();
    QJsonObject::iterator it;

    QObject::disconnect(this, SIGNAL(toggled(bool)), this, SLOT(checkChange(bool)));
    for (it = json.begin(); it != json.end(); ++it)
    {
        QJsonObject role = (*it).toObject();

        if (role["id"].toInt() == _roleID)
            this->setChecked(true);
        else
            this->setChecked(false);
    }
    QObject::connect(this, SIGNAL(toggled(bool)), this, SLOT(checkChange(bool)));
}

void UserRoleCheckbox::Failure(int id, QByteArray data)
{
    QMessageBox::critical(this, tr("Connexion Error"), tr("Failure to retreive data from internet"));
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
