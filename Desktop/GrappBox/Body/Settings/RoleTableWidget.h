#ifndef ROLETABLEWIDGET_H
#define ROLETABLEWIDGET_H

#include "UserRoleCheckbox.h"
#include "CreateNewRole.h"
#include "InfoPushButton.h"
#include "InviteUserWindow.h"
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
#include <QPair>

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
    void                        SuccessDeleteRole(int id, QByteArray data);
    void                        SuccessAttachRole(int id, QByteArray data);
    void                        SuccessInviteUser(int id, QByteArray data);
    void                        SuccessDetachRole(int id, QByteArray data);
    void                        SuccessDeleteUser(int id, QByteArray data);
    void                        Failure(int id, QByteArray data);
    void                        FailureAddRole(int id, QByteArray data);
    void                        FailureDeleteRole(int id, QByteArray data);
    void                        FailureAttachRole(int id, QByteArray data);
    void                        FailureDetachRole(int id, QByteArray data);
    void                        InviteUser(QString usermail);
    void                        deleteUser(int);
    void                        deleteRole(int);
    void                        AssignRole(UserRoleCheckbox *,const QPair<const QString &, const int>, const QPair<const QString &, const int>);
    void                        DetachRole(UserRoleCheckbox *,const QPair<const QString &, const int>, const QPair<const QString &, const int>);

private:
    QMap<int, QString>          *_roles;
    QMap<int, QString>          *_users;
    QList<UserRoleCheckbox *>   *_usersRolesCheckboxes;
    QVBoxLayout                 *_rowLayout;
    QList<QHBoxLayout *>        *_colLayout;
    QPushButton                 *_newUserBtn;
    QPushButton                 *_newRoleBtn;
    CreateNewRole               *_newRoleWindow;
    InviteUserWindow            *_inviteUserWindow;
    API::IDataConnector         *_api;
    int                         _projectId;
    QList<QString>              _stackRole;
    QList<int>                  _stackRoleDelete;
    QList<UserRoleCheckbox *>   _stackRoleAssign;
    QList<UserRoleCheckbox *>   _stackRoleDetach;
};

#endif // ROLETABLEWIDGET_H

