#include "ProjectSettingsModel.h"

ProjectSettingsModel::ProjectSettingsModel(QObject *parent) : QObject(parent)
{
    m_project = new ProjectData();
    m_isLoading = 0;
}

void ProjectSettingsModel::loadInformation()
{
    BEGIN_REQUEST_ADV(this, "onLoadProjectInfoDone", "onLoadProjectInfoFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECT);
    }
    END_REQUEST;
    BEGIN_REQUEST_ADV(this, "onLoadProjectRolesDone", "onLoadProjectRolesFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECT_ROLE);
    }
    END_REQUEST;
    BEGIN_REQUEST_ADV(this, "onLoadCustomerAccessDone", "onLoadCustomerAccessFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_CUSTOMER_ACCESSES);
    }
    END_REQUEST;
    m_isLoading += 3;
    emit isLoadingChanged(m_isLoading != 0);
}

void ProjectSettingsModel::modifyInformation(QString title, QString description, QString company, QString email, QString phone, QString facebook, QString twitter)
{
    BEGIN_REQUEST_ADV(this, "onModifyInformationDone", "onModifyInformationFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", m_idProject);
        ADD_FIELD("name", title);
        ADD_FIELD("description", description);
        ADD_FIELD("phone", phone);
        ADD_FIELD("company", company);
        ADD_FIELD("facebook", facebook);
        ADD_FIELD("email", email);
        ADD_FIELD("twitter", twitter);
        PUT(API::DP_PROJECT, API::PUTR_EDIT_PROJECT);
    }
    END_REQUEST;
}

void ProjectSettingsModel::addUser(QString users)
{
    m_isLoading++;
    BEGIN_REQUEST_ADV(this, "onAddUserDone", "onAddUserFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("id", m_idProject);
        ADD_FIELD("email", users);
        POST(API::DP_PROJECT, API::PR_ADD_USER_PROJECT);
    }
    END_REQUEST;
}

void ProjectSettingsModel::deleteUser(int idUser)
{
    m_isLoading++;
    BEGIN_REQUEST_ADV(this, "onDeleteUserDone", "onDeleteUserFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        ADD_URL_FIELD(idUser);
        DELETE_REQ(API::DP_PROJECT, API::DR_PROJECT_USER);
    }
    END_REQUEST;
}

void ProjectSettingsModel::changeRoleUser(int idUser, int idRole, int oldIdRole)
{
    m_isLoading++;
    if (oldIdRole == -1)
    {
        BEGIN_REQUEST_ADV(this, "onChangeRoleUserDone", "onChangeRoleUserFail");
        {
            EPURE_WARNING_INDEX
            ADD_FIELD("token", USER_TOKEN);
            ADD_FIELD("userId", idUser);
            ADD_FIELD("roleId", idRole);
            POST(API::DP_PROJECT, API::PR_ROLE_ASSIGN);
        }
        END_REQUEST;
    }
    else
    {
        BEGIN_REQUEST_ADV(this, "onChangeRoleUserDone", "onChangeRoleUserFail");
        {
            EPURE_WARNING_INDEX
            ADD_FIELD("token", USER_TOKEN);
            ADD_FIELD("projectId", m_idProject);
            ADD_FIELD("userId", idUser);
            ADD_FIELD("old_roleId", oldIdRole);
            ADD_FIELD("roleId", idRole);
            PUT(API::DP_PROJECT, API::PUTR_ROLE_USER);
        }
        END_REQUEST;
    }
}

void ProjectSettingsModel::addCustomerAccess(QString name)
{
    BEGIN_REQUEST_ADV(this, "onAddCustomerAccessDone", "onAddCustomerAccessFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", m_idProject);
        ADD_FIELD("name", name);
        POST(API::DP_PROJECT, API::PR_CUSTOMER_GENERATE_ACCESS);
    }
    END_REQUEST;
}

void ProjectSettingsModel::removeCustomerAccess(int idCustomer)
{
    BEGIN_REQUEST_ADV(this, "onRemoveCustomerAccessDone", "onRemoveCustomerAccessFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        ADD_URL_FIELD(idCustomer);
        DELETE_REQ(API::DP_PROJECT, API::DR_CUSTOMER_ACCESS);
    }
    END_REQUEST;
}

void ProjectSettingsModel::addNewRole(QString name, QList<int> roleValue)
{
    BEGIN_REQUEST_ADV(this, "onAddNewRoleDone", "onAddNewRoleFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("projectId", m_idProject);
        ADD_FIELD("name", name);
        int i = 0;
        for (QString var : ROLE_API_ARRAY_ADD)
        {
            ADD_FIELD(var, roleValue[i]);
            ++i;
        }
        GENERATE_JSON_DEBUG;
        POST(API::DP_PROJECT, API::PR_ROLE_ADD);
    }
    END_REQUEST;
}

void ProjectSettingsModel::deleteRole(int id)
{
    BEGIN_REQUEST_ADV(this, "onDeleteRoleDone", "onDeleteRoleFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(id);
        DELETE_REQ(API::DP_PROJECT, API::DR_PROJECT_ROLE);
    }
    END_REQUEST;
}

void ProjectSettingsModel::updateRole(int id, QList<int> roleValue)
{
    BEGIN_REQUEST_ADV(this, "onUpdateRoleDone", "onUpdateRoleFail");
    {
        EPURE_WARNING_INDEX
        ADD_FIELD("token", USER_TOKEN);
        ADD_FIELD("roleId", id);
        int i = 0;
        for (QString var : ROLE_API_ARRAY_ADD)
        {
            ADD_FIELD(var, roleValue[i]);
            ++i;
        }
        GENERATE_JSON_DEBUG;
        PUT(API::DP_PROJECT, API::PUTR_ROLE);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onLoadProjectInfoDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    m_isLoading--;
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    if (m_project == nullptr)
        m_project = new ProjectData(obj);
    else
        m_project->modifyByJsonObject(obj);
    emit projectChanged(m_project);

    BEGIN_REQUEST_ADV(this, "onLoadUsersProjectDone", "onLoadUsersProjectFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECTS_USER);
    }
    END_REQUEST;
    m_isLoading++;
    emit isLoadingChanged(m_isLoading != 0);
}

void ProjectSettingsModel::onLoadProjectInfoFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading--;
    emit isLoadingChanged(m_isLoading != 0);
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onLoadUserRoleDone(int id, QByteArray data)
{
    m_isLoading--;
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    int idRole;
    if (obj["array"].toArray().size() == 0)
    {
        idRole = -1;
    }
    else
        idRole = obj["array"].toArray()[0].toObject()["id"].toInt();
    int idUser = m_userRolesGet[id];
    m_userRolesGet.remove(id);
    QVariantList users = project()->users();
    for (QVariant item : users)
    {
        UserData *user = qobject_cast<UserData*>(item.value<UserData*>());
        if (user && user->id() == idUser)
        {
            user->setRoleId(idRole);
        }
    }
    emit isLoadingChanged(m_isLoading != 0);
}

void ProjectSettingsModel::onLoadUserRoleFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading--;
    emit isLoadingChanged(m_isLoading != 0);
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onLoadProjectRolesDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    m_isLoading--;
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QList<int> idToKeep;
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject item = ref.toObject();
        int idRole = item["id"].toInt();
        idToKeep.push_back(idRole);
        RolesData *selRoles = nullptr;
        for (RolesData *roles : m_roles)
            if (roles->id() == idRole)
            {
                selRoles = roles;
                break;
            }
        if (selRoles)
            selRoles->modifyByJsonObject(item);
        else
            m_roles.push_back(new RolesData(item));
    }
    QList<RolesData*> toDelete;
    for (RolesData *item : m_roles)
        if (!idToKeep.contains(item->id()))
            toDelete.push_back(item);
    for (RolesData *item : toDelete)
        m_roles.removeAll(item);
    emit rolesChanged(roles());
    emit isLoadingChanged(m_isLoading != 0);
}

void ProjectSettingsModel::onLoadProjectRolesFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading--;
    emit isLoadingChanged(m_isLoading != 0);
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onLoadCustomerAccessDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    m_isLoading--;
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QList<int> idToKeep;
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject item = ref.toObject();
        int idCuAccess = item["id"].toInt();
        idToKeep.push_back(idCuAccess);
        CustomerAccessData *selCAccess = nullptr;
        for (CustomerAccessData *custAccess : m_customersAccess)
            if (custAccess->id() == idCuAccess)
            {
                selCAccess = custAccess;
                break;
            }
        if (selCAccess)
            selCAccess->modifyByJsonObject(item);
        else
            m_customersAccess.push_back(new CustomerAccessData(item));
    }
    QList<CustomerAccessData*> toDelete;
    for (CustomerAccessData *item : m_customersAccess)
        if (!idToKeep.contains(item->id()))
            toDelete.push_back(item);
    for (CustomerAccessData *item : toDelete)
        m_customersAccess.removeAll(item);
    emit customersAccessChanged(customersAccess());
    emit isLoadingChanged(m_isLoading != 0);
}

void ProjectSettingsModel::onLoadCustomerAccessFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading--;
    emit isLoadingChanged(m_isLoading != 0);
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onLoadUsersProjectDone(int id, QByteArray data)
{
	Q_UNUSED(id)
    m_isLoading--;
    QJsonDocument doc;
    doc = QJsonDocument::fromJson(data);
    QJsonObject obj = doc.object()["data"].toObject();
    QList<int> idToKeep;
    QList<UserData*> users;
    for (QVariant item : project()->users())
        users.push_back(qobject_cast<UserData*>(item.value<UserData*>()));
    for (QJsonValueRef ref : obj["array"].toArray())
    {
        QJsonObject item = ref.toObject();
        int idUser = item["id"].toInt();
        idToKeep.push_back(idUser);
        UserData *selUser = nullptr;
        for (UserData *user : users)
            if (user->id() == idUser)
            {
                selUser = user;
                break;
            }
        if (selUser)
        {
            selUser->setId(item["id"].toInt());
            selUser->setFirstName(item["firstname"].toString());
            selUser->setLastName(item["lastname"].toString());
        }
        else
        {
            selUser = new UserData();
            selUser->setId(item["id"].toInt());
            selUser->setFirstName(item["firstname"].toString());
            selUser->setLastName(item["lastname"].toString());
            users.push_back(selUser);
        }
        m_isLoading++;
    }
    QList<UserData*> toDelete;
    for (UserData *item : users)
        if (!idToKeep.contains(item->id()))
            toDelete.push_back(item);
    for (UserData *item : toDelete)
        users.removeAll(item);
    QVariantList newUsers;
    for (UserData *item : users)
    {
        BEGIN_REQUEST_ADV(this, "onLoadUserRoleDone", "onLoadUserRoleFail");
        {
            ADD_URL_FIELD(USER_TOKEN);
            ADD_URL_FIELD(m_idProject);
            ADD_URL_FIELD(item->id());
            m_userRolesGet[GET(API::DP_PROJECT, API::GR_PROJECT_USER_ROLE)] = item->id();
        }
        END_REQUEST;
        newUsers.push_back(qVariantFromValue(item));
    }
    m_project->setUsers(newUsers);
    emit isLoadingChanged(m_isLoading != 0);
}

void ProjectSettingsModel::onLoadUsersProjectFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading--;
    emit isLoadingChanged(m_isLoading != 0);
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onModifyInformationDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    loadInformation();
}

void ProjectSettingsModel::onModifyInformationFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onAddUserDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    BEGIN_REQUEST_ADV(this, "onLoadUsersProjectDone", "onLoadUsersProjectFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECTS_USER);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onAddUserFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onDeleteUserDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    BEGIN_REQUEST_ADV(this, "onLoadUsersProjectDone", "onLoadUsersProjectFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECTS_USER);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onDeleteUserFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onChangeRoleUserDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    BEGIN_REQUEST_ADV(this, "onLoadUsersProjectDone", "onLoadUsersProjectFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECTS_USER);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onChangeRoleUserFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onAddCustomerAccessDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading++;
    BEGIN_REQUEST_ADV(this, "onLoadCustomerAccessDone", "onLoadCustomerAccessFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_CUSTOMER_ACCESSES);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onAddCustomerAccessFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onRemoveCustomerAccessDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading++;
    BEGIN_REQUEST_ADV(this, "onLoadCustomerAccessDone", "onLoadCustomerAccessFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_CUSTOMER_ACCESSES);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onRemoveCustomerAccessFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onAddNewRoleDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading++;
    BEGIN_REQUEST_ADV(this, "onLoadProjectRolesDone", "onLoadProjectRolesFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECT_ROLE);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onAddNewRoleFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onDeleteRoleDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading++;
    BEGIN_REQUEST_ADV(this, "onLoadProjectRolesDone", "onLoadProjectRolesFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECT_ROLE);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onDeleteRoleFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}

void ProjectSettingsModel::onUpdateRoleDone(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    m_isLoading++;
    BEGIN_REQUEST_ADV(this, "onLoadProjectRolesDone", "onLoadProjectRolesFail");
    {
        ADD_URL_FIELD(USER_TOKEN);
        ADD_URL_FIELD(m_idProject);
        GET(API::DP_PROJECT, API::GR_PROJECT_ROLE);
    }
    END_REQUEST;
}

void ProjectSettingsModel::onUpdateRoleFail(int id, QByteArray data)
{
	Q_UNUSED(id)
	Q_UNUSED(data)
    SInfoManager::GetManager()->emitError("Project settings", "Somethings went wrong. Maybe you don't have the access to this part or this action.");
}
