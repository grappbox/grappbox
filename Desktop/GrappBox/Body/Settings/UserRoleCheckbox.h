#ifndef USERROLECHECKBOX_H
#define USERROLECHECKBOX_H

#include <QCheckBox>
#include <QPair>

class UserRoleCheckbox : public QCheckBox
{
    Q_OBJECT
public:
    explicit UserRoleCheckbox(QWidget *parent = 0);

    void                                    SetUser(const QString &username, const int ID);
    void                                    SetRole(const QString &roleName, const int ID);
    const QPair<const QString &, const int> getUser();
    const QPair<const QString &, const int> getRole();

public slots:
    void    checkChange(bool);

signals:
    void    checked(UserRoleCheckbox *, const QPair<const QString &, const int> user, const QPair<const QString &, const int> role);
    void    unchecked(UserRoleCheckbox *,const QPair<const QString &, const int> user, const QPair<const QString &, const int> role);

private:
    QString                                 _userName;
    int                                     _userID;
    QString                                 _roleName;
    int                                     _roleID;
};

#endif // USERROLECHECKBOX_H
