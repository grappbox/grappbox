#ifndef USERROLECHECKBOX_H
#define USERROLECHECKBOX_H

#include <QCheckBox>
#include <QPair>
#include "SDataManager.h"
#include "IDataConnector.h"
#include <QJsonDocument>
#include <QJsonObject>
#include <QMessageBox>

class UserRoleCheckbox : public QCheckBox
{
    Q_OBJECT
public:
    explicit UserRoleCheckbox(QWidget *parent = 0);

    void                                    InitEnd(int projectId);
    void                                    SetUser(const QString &username, const int ID);
    void                                    SetRole(const QString &roleName, const int ID);
    const QPair<const QString &, const int> getUser();
    const QPair<const QString &, const int> getRole();

public slots:
    void    checkChange(bool);
    void    SuccessInit(int id, QByteArray data);
    void    Failure(int id, QByteArray data);

signals:
    void    checked(UserRoleCheckbox *, const QPair<const QString &, const int> user, const QPair<const QString &, const int> role);
    void    unchecked(UserRoleCheckbox *,const QPair<const QString &, const int> user, const QPair<const QString &, const int> role);

private:
    QString                                 _userName;
    int                                     _userID;
    QString                                 _roleName;
    int                                     _roleID;
    API::IDataConnector                     *_api;
};

#endif // USERROLECHECKBOX_H
