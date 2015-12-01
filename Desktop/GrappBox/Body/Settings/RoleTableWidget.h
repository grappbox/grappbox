#ifndef ROLETABLEWIDGET_H
#define ROLETABLEWIDGET_H

#include "UserRoleCheckbox.h"
#include "CreateNewRole.h"
#include "InfoPushButton.h"
#include "API/IDataConnector.h"
#include "API/SDataManager.h"
#include <QWidget>
#include <QList>
#include <QString>
#include <QMap>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QLabel>
#include <QPushButton>
#include <QLayoutItem>
#include <QJsonDocument>
#include <QJsonObject>
#include <QMessageBox>
#include <QDebug>

class RoleTableWidget: public QWidget
{
    Q_OBJECT
public:
    explicit                    RoleTableWidget(QWidget *parent = 0);
    virtual                     ~RoleTableWidget();

    void                        setRoles(const QMap<int, QString> &roles);
    void                        setUsers(const QMap<int, QString> &users);
    void                        addRole(const QString& role, const int ID);
    void                        addUser(const QString& user, const int ID);
    const QMap<int, QList<int>> getRolesByUsers(); //<ID user, List ID roles>
    const QMap<int, QString>    &getRoles();
    const QMap<int, QString>    &getUsers();
    void                        Clear();
    void                        SetProjectId(int id);

private:
    void ClearLayout(QLayout* layout, bool deleteWidgets = true);

public slots:
    void                        reset();
    void                        refresh();
    void                        NewRoleTriggered();
    void                        SuccessAddRole(int id, QByteArray data);
    void                        Failure(int id, QByteArray data);
    void                        inviteUser();
    void                        deleteUser(int);
    void                        deleteRole(int);

private:
    QMap<int, QString>          *_roles;
    QMap<int, QString>          *_users;
    QList<UserRoleCheckbox *>   *_usersRolesCheckboxes;
    QVBoxLayout                 *_rowLayout;
    QList<QHBoxLayout *>        *_colLayout;
    QPushButton                 *_newUserBtn;
    QPushButton                 *_newRoleBtn;
    CreateNewRole               *_newRoleWindow;
    API::IDataConnector         *_api;
    int                         _projectId;
    QList<QString>              _stackRole;
};

#endif // ROLETABLEWIDGET_H

