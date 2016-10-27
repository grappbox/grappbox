#ifndef PROJECTSETTINGSMODEL_H
#define PROJECTSETTINGSMODEL_H

#include <QObject>
#include <QCryptographicHash>
#include "API/SDataManager.h"
#include "ProjectData.h"

#define ROLE_API_ARRAY {"teamTimeline", "customerTimeline", "gantt", "whiteboard", "bugtracker", "event", "task", "projectSettings", "cloud"}
#define ROLE_API_ARRAY_ADD {"teamTimeline", "customerTimeline", "gantt", "whiteboard", "bugtracker", "event", "task", "projectSettings", "cloud"}

class RolesData : public QObject
{
    Q_OBJECT
    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QVariantList accessRight READ accessRight WRITE setAccessRight NOTIFY accessRightChanged)
    Q_PROPERTY(QString name READ name WRITE setName NOTIFY nameChanged)

public:
    explicit RolesData(QObject *parent = 0)
    {
    Q_UNUSED(parent)
        m_name = "";
    }

    RolesData(QJsonObject obj)
    {
        modifyByJsonObject(obj);
    }

    void modifyByJsonObject(QJsonObject obj)
    {
        m_id = obj["roleId"].toInt();
        m_name = obj["name"].toString();
        QVariantList roles;
        QString rolesStr[] = ROLE_API_ARRAY;
        for (QString var : rolesStr)
        {
            roles.push_back(obj[var].toInt());
        }
        m_accessRight = roles;
        emit idChanged(id());
        emit nameChanged(name());
        emit accessRightChanged(accessRight());
    }

    QVariantList accessRight() const
    {
        return m_accessRight;
    }

    QString name() const
    {
        return m_name;
    }

    int id() const
    {
        return m_id;
    }

signals:

        void accessRightChanged(QVariantList accessRight);

        void nameChanged(QString name);

        void idChanged(int id);

public slots:

    void setAccessRight(QVariantList accessRight)
    {
        if (m_accessRight == accessRight)
            return;

        m_accessRight = accessRight;
        emit accessRightChanged(accessRight);
    }

    void setName(QString name)
    {
        if (m_name == name)
            return;

        m_name = name;
        emit nameChanged(name);
    }

    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }

private:

    QVariantList m_accessRight;
    QString m_name;
    int m_id;
};

class CustomerAccessData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QString name READ name WRITE setName NOTIFY nameChanged)
    Q_PROPERTY(QString token READ token WRITE setToken NOTIFY tokenChanged)

public:
    explicit CustomerAccessData(QObject *parent = 0)
    {
    Q_UNUSED(parent)

    }

    CustomerAccessData(QJsonObject obj)
    {
        modifyByJsonObject(obj);
    }

    void modifyByJsonObject(QJsonObject obj)
    {
        m_id = obj["id"].toInt();
        m_name = obj["name"].toString();
        m_token = obj["customer_token"].toString();
        emit idChanged(id());
        emit nameChanged(name());
        emit tokenChanged(token());
    }

    int id() const
    {
        return m_id;
    }

    QString name() const
    {
        return m_name;
    }

    QString token() const
    {
        return m_token;
    }

signals:

    void idChanged(int id);

    void nameChanged(QString name);

    void tokenChanged(QString token);

public slots:

void setId(int id)
{
    if (m_id == id)
        return;

    m_id = id;
    emit idChanged(id);
}

void setName(QString name)
{
    if (m_name == name)
        return;

    m_name = name;
    emit nameChanged(name);
}

void setToken(QString token)
{
    if (m_token == token)
        return;

    m_token = token;
    emit tokenChanged(token);
}

private:

int m_id;
QString m_name;
QString m_token;
};

class ProjectSettingsModel : public QObject
{
    Q_OBJECT
    Q_PROPERTY(int idProject READ idProject WRITE setIdProject NOTIFY idProjectChanged)
    Q_PROPERTY(ProjectData* project READ project WRITE setProject NOTIFY projectChanged)
    Q_PROPERTY(QVariantList roles READ roles WRITE setRoles NOTIFY rolesChanged)
    Q_PROPERTY(QVariantList customersAccess READ customersAccess WRITE setCustomersAccess NOTIFY customersAccessChanged)
    Q_PROPERTY(bool isLoading READ isLoading NOTIFY isLoadingChanged)

public:
    explicit ProjectSettingsModel(QObject *parent = 0);

    Q_INVOKABLE void loadInformation();
    Q_INVOKABLE void modifyInformation(QString title, QString description, QString company, QString email, QString phone, QString facebook, QString twitter, QString avatar);
    Q_INVOKABLE void addUser(QString users);
    Q_INVOKABLE void deleteUser(int idUser);
    Q_INVOKABLE void changeRoleUser(int idUser, int idRole, int oldIdRole);
    Q_INVOKABLE void addCustomerAccess(QString name);
    Q_INVOKABLE void removeCustomerAccess(int idCustomer);
    Q_INVOKABLE void addNewRole(QString name, QList<int> roleValue);
    Q_INVOKABLE void deleteRole(int id);
    Q_INVOKABLE void updateRole(int id, QList<int> roleValue);
    Q_INVOKABLE void changePassword(QString oldPass, QString newPass);
    Q_INVOKABLE void leaveProject();
    Q_INVOKABLE void deleteProject();

    ProjectData* project() const { return m_project; }
    QVariantList roles() const
    {
        QVariantList ret;
        for (RolesData *item : m_roles)
            ret.push_back(qVariantFromValue(item));
        return ret;
    }
    QVariantList customersAccess() const
    {
        QVariantList ret;
        for (CustomerAccessData *item : m_customersAccess)
            ret.push_back(qVariantFromValue(item));
        return ret;
    }

    int idProject() const
    {
        return m_idProject;
    }

    bool isLoading() const
    {
        return m_isLoading != 0;
    }

signals:
    void projectChanged(ProjectData* project);
    void rolesChanged(QVariantList roles);
    void customersAccessChanged(QVariantList customersAccess);

    void idProjectChanged(int idProject);

    void isLoadingChanged(bool isLoading);

public slots:
    void setProject(ProjectData* project)
    {
        if (m_project == project)
            return;

        m_project = project;
        emit projectChanged(project);
    }
    void setRoles(QVariantList roles)
    {
        m_roles.clear();
        for (QVariant var : roles)
        {
            RolesData *item = qobject_cast<RolesData*>(var.value<RolesData*>());
            if (item)
                m_roles.push_back(item);
        }
        emit rolesChanged(roles);
    }
    void setCustomersAccess(QVariantList customersAccess)
    {
        m_customersAccess.clear();
        for (QVariant var : customersAccess)
        {
            CustomerAccessData *item = qobject_cast<CustomerAccessData*>(var.value<CustomerAccessData*>());
            if (item)
                m_customersAccess.push_back(item);
        }
        emit customersAccessChanged(customersAccess);
    }

    void onLoadProjectInfoDone(int id, QByteArray data);
    void onLoadProjectInfoFail(int id, QByteArray data);
    void onLoadUserRoleDone(int id, QByteArray data);
    void onLoadUserRoleFail(int id, QByteArray data);
    void onLoadProjectRolesDone(int id, QByteArray data);
    void onLoadProjectRolesFail(int id, QByteArray data);
    void onLoadCustomerAccessDone(int id, QByteArray data);
    void onLoadCustomerAccessFail(int id, QByteArray data);
    void onLoadUsersProjectDone(int id, QByteArray data);
    void onLoadUsersProjectFail(int id, QByteArray data);
    void onModifyInformationDone(int id, QByteArray data);
    void onModifyInformationFail(int id, QByteArray data);
    void onAddUserDone(int id, QByteArray data);
    void onAddUserFail(int id, QByteArray data);
    void onDeleteUserDone(int id, QByteArray data);
    void onDeleteUserFail(int id, QByteArray data);
    void onChangeRoleUserDone(int id, QByteArray data);
    void onChangeRoleUserFail(int id, QByteArray data);
    void onAddCustomerAccessDone(int id, QByteArray data);
    void onAddCustomerAccessFail(int id, QByteArray data);
    void onRemoveCustomerAccessDone(int id, QByteArray data);
    void onRemoveCustomerAccessFail(int id, QByteArray data);
    void onAddNewRoleDone(int id, QByteArray data);
    void onAddNewRoleFail(int id, QByteArray data);
    void onDeleteRoleDone(int id, QByteArray data);
    void onDeleteRoleFail(int id, QByteArray data);
    void onUpdateRoleDone(int id, QByteArray data);
    void onUpdateRoleFail(int id, QByteArray data);

    void setIdProject(int idProject)
    {
        if (m_idProject == idProject)
            return;

        m_idProject = idProject;
        emit idProjectChanged(idProject);
    }

private:
    ProjectData* m_project;
    QList<RolesData*> m_roles;
    QList<CustomerAccessData*> m_customersAccess;

    QMap<int, int> m_userRolesGet;



    int m_idProject;
    int m_isLoading;
};

#endif // PROJECTSETTINGSMODEL_H
