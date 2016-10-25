#ifndef USERDATA_H
#define USERDATA_H

#include <QImage>
#include <QObject>
#include <QDebug>
#include <QDate>

class UserData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QString firstName READ firstName WRITE setFirstName NOTIFY firstNameChanged)
    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QString lastName READ lastName WRITE setLastName NOTIFY lastNameChanged)
    Q_PROPERTY(QDate birthday READ birthday WRITE setBirthday NOTIFY birthdayChanged)
    Q_PROPERTY(QString twitter READ twitter WRITE setTwitter NOTIFY twitterChanged)
    Q_PROPERTY(QString linkedin READ linkedin WRITE setLinkedin NOTIFY linkedinChanged)
    Q_PROPERTY(QString viadeo READ viadeo WRITE setViadeo NOTIFY viadeoChanged)
    Q_PROPERTY(QString mail READ mail WRITE setMail NOTIFY mailChanged)
    Q_PROPERTY(QString phone READ phone WRITE setPhone NOTIFY phoneChanged)
    Q_PROPERTY(QString country READ country WRITE setCountry NOTIFY countryChanged)
    Q_PROPERTY(int roleId READ roleId WRITE setRoleId NOTIFY roleIdChanged)
    Q_PROPERTY(int occupation READ occupation WRITE setOccupation NOTIFY occupationChanged)
    Q_PROPERTY(QDateTime avatarDate READ avatarDate WRITE setAvatarDate NOTIFY avatarDateChanged)

public:
    UserData();
    UserData(const UserData &copy) : QObject(nullptr)
    {
        m_firstName = copy.m_firstName;
        m_lastName = copy.m_lastName;
        m_id = copy.m_id;
        m_birthday = copy.m_birthday;
        m_twitter = copy.m_twitter;
        m_linkedin = copy.m_linkedin;
        m_viadeo = copy.m_viadeo;
        m_roleId = copy.m_roleId;
    }

    UserData &operator=(const UserData& copy)
    {
        m_firstName = copy.m_firstName;
        m_lastName = copy.m_lastName;
        m_id = copy.m_id;
        m_birthday = copy.m_birthday;
        m_twitter = copy.m_twitter;
        m_linkedin = copy.m_linkedin;
        m_viadeo = copy.m_viadeo;
        m_roleId = copy.m_roleId;
        return *this;
    }

    UserData(int id, QString firstName, QString lastName);

    QString firstName() const
    {
        return m_firstName;
    }

    int id() const
    {
        return m_id;
    }

    QString lastName() const
    {
        return m_lastName;
    }

    QDate birthday() const
    {
        return m_birthday;
    }

    QString twitter() const
    {
        return m_twitter;
    }

    QString linkedin() const
    {
        return m_linkedin;
    }

    QString viadeo() const
    {
        return m_viadeo;
    }

    int occupation() const
    {
        return m_occupation;
    }

    QString mail() const
    {
        return m_mail;
    }

    QString phone() const
    {
        return m_phone;
    }

    QString country() const
    {
        return m_country;
    }

    int roleId() const
    {
        return m_roleId;
    }

    QDateTime avatarDate() const
    {
        return m_avatarDate;
    }

signals:

    void firstNameChanged(QString firstName);

    void idChanged(int id);

    void lastNameChanged(QString lastName);

    void birthdayChanged(QDate birthday);

    void twitterChanged(QString twitter);

    void linkedinChanged(QString linkedin);

    void viadeoChanged(QString viadeo);

    void occupationChanged(int occupation);

    void mailChanged(QString mail);

    void phoneChanged(QString phone);

    void countryChanged(QString country);

    void roleIdChanged(int roleId);

    void avatarDateChanged(QDateTime avatarDate);

public slots:

    void setFirstName(QString firstName)
    {
        if (m_firstName == firstName)
            return;

        m_firstName = firstName;
        emit firstNameChanged(firstName);
    }

    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }

    void setLastName(QString lastName)
    {
        if (m_lastName == lastName)
            return;

        m_lastName = lastName;
        emit lastNameChanged(lastName);
    }

    void setBirthday(QDate birthday)
    {
        if (m_birthday == birthday)
            return;

        m_birthday = birthday;
        emit birthdayChanged(birthday);
    }

    void setTwitter(QString twitter)
    {
        if (m_twitter == twitter)
            return;

        m_twitter = twitter;
        emit twitterChanged(twitter);
    }

    void setLinkedin(QString linkedin)
    {
        if (m_linkedin == linkedin)
            return;

        m_linkedin = linkedin;
        emit linkedinChanged(linkedin);
    }

    void setViadeo(QString viadeo)
    {
        if (m_viadeo == viadeo)
            return;

        m_viadeo = viadeo;
        emit viadeoChanged(viadeo);
    }

    void setOccupation(int occupation)
    {
        if (m_occupation == occupation)
            return;

        m_occupation = occupation;
        emit occupationChanged(occupation);
    }

    void setMail(QString mail)
    {
        if (m_mail == mail)
            return;

        m_mail = mail;
        emit mailChanged(mail);
    }

    void setPhone(QString phone)
    {
        if (m_phone == phone)
            return;

        m_phone = phone;
        emit phoneChanged(phone);
    }

    void setCountry(QString country)
    {
        if (m_country == country)
            return;

        m_country = country;
        emit countryChanged(country);
    }

    void setRoleId(int roleId)
    {
        if (m_roleId == roleId)
            return;
        qDebug() << "Role id changed";
        m_roleId = roleId;
        emit roleIdChanged(roleId);
    }

    void setAvatarDate(QDateTime avatarDate)
    {
        if (m_avatarDate == avatarDate)
            return;

        m_avatarDate = avatarDate;
        emit avatarDateChanged(avatarDate);
    }

private:/*
    int m_id;
    QString m_firstName;
    QString m_lastName;
    QDate m_birthday;
    QImage m_avatar;
    QString m_email;
    QString m_phone;
    QString m_country;
    QString m_linkedIn;
    QString m_viadeo;
    QString m_twitter;*/
    QString m_firstName;
    int m_id;
    QString m_lastName;
    QDate m_birthday;
    QString m_twitter;
    QString m_linkedin;
    QString m_viadeo;
    int m_occupation;
    QString m_mail;
    QString m_phone;
    QString m_country;
    int m_roleId;
    QDateTime m_avatarDate;
};

Q_DECLARE_METATYPE(UserData)
Q_DECLARE_METATYPE(UserData*)

#endif // USERDATA_H
