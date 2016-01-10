#ifndef BUGUSER_H
#define BUGUSER_H

#include <QString>
#include <QByteArray>
#include <QImage>

class BugUser
{
public:
    BugUser();
    explicit BugUser(const int id, const QString &name, const QString &email, const QByteArray &avatar);
    bool operator==(const BugUser &);

    const int GetId() const;
    const QString &GetName() const;
    const QString &GetEmail() const;
    const QImage &GetAvatar() const;

    void SetName(const QString &name);
    void SetEmail(const QString &email);
    void SetAvatar(const QByteArray &avatar);

private:
    int     _id;
    QString _name;
    QString _email;
    QImage  _avatar;
};

#endif // BUGUSER_H
