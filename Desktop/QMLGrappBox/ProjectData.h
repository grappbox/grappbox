#ifndef PROJECTDATA
#define PROJECTDATA

#include "UserData.h"
#include <QObject>
#include <QDate>
#include <QColor>
#include <QDate>
#include <QVariant>
#include <QVariantList>
#include <QStringList>

class ProjectData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(QString name READ name WRITE setName NOTIFY nameChanged)
    Q_PROPERTY(QString description READ description WRITE setDescription NOTIFY descriptionChanged)
    Q_PROPERTY(QString phone READ phone WRITE setPhone NOTIFY phoneChanged)
    Q_PROPERTY(QString company READ company WRITE setCompany NOTIFY companyChanged)
    Q_PROPERTY(QString mail READ mail WRITE setMail NOTIFY mailChanged)
    Q_PROPERTY(QString facebook READ facebook WRITE setFacebook NOTIFY facebookChanged)
    Q_PROPERTY(QString twitter READ twitter WRITE setTwitter NOTIFY twitterChanged)
    Q_PROPERTY(QColor color READ color WRITE setColor NOTIFY colorChanged)
    Q_PROPERTY(QDate creationDate READ creationDate WRITE setCreationDate NOTIFY creationDateChanged)
    Q_PROPERTY(QDate deleteDate READ deleteDate WRITE setDeleteDate NOTIFY deleteDateChanged)
    Q_PROPERTY(QVariantList users READ users WRITE setUsers NOTIFY usersChanged)
    Q_PROPERTY(int numTaskOnGoing READ numTaskOnGoing WRITE setNumTaskOnGoing NOTIFY numTaskOnGoingChanged)
    Q_PROPERTY(int numTaskFinished READ numTaskFinished WRITE setNumTaskFinished NOTIFY numTaskFinishedChanged)
    Q_PROPERTY(int numTaskTotal READ numTaskTotal WRITE setNumTaskTotal NOTIFY numTaskTotalChanged)
    Q_PROPERTY(int numBugTotal READ numBugTotal WRITE setNumBugTotal NOTIFY numBugTotalChanged)
    Q_PROPERTY(int numMessageTimeline READ numMessageTimeline WRITE setNumMessageTimeline NOTIFY numMessageTimelineChanged)

public:
    ProjectData()
    {

    }

    Q_INVOKABLE QStringList usersName()
    {
        m_nameList.clear();
        for (QVariant userTmp : m_users)
        {
            UserData *d = userTmp.value<UserData*>();
            m_nameList.push_back(QString(d->firstName() + " " + d->lastName()));
        }
        return m_nameList;
    }

    Q_INVOKABLE int getUserDataByIndex(int index)
    {
        int listIndex = 0;
        for (QVariant userTmp : m_users)
        {
            UserData *d = userTmp.value<UserData*>();

            if (listIndex == index)
                return listIndex;
            listIndex++;
        }
        return 0;
    }

    Q_INVOKABLE int getIndexByUserData(int id)
    {
        for (QVariant userTmp : m_users)
        {
            UserData *d = userTmp.value<UserData*>();
            if (d->id() == id)
                return m_users.indexOf(userTmp);
        }
        return 0;
    }

    ProjectData(const ProjectData &copy)
    {
        m_id = copy.m_id;
        m_name = copy.m_name;
        m_description = copy.m_description;
        m_phone = copy.m_phone;
        m_company = copy.m_company;
        m_mail = copy.m_mail;
        m_facebook = copy.m_facebook;
        m_twitter = copy.m_twitter;
        m_color = copy.m_color;
        m_creationDate = copy.m_creationDate;
        m_deleteDate = copy.m_deleteDate;
    }

    ProjectData &operator=(const ProjectData &copy)
    {
        m_id = copy.m_id;
        m_name = copy.m_name;
        m_description = copy.m_description;
        m_phone = copy.m_phone;
        m_company = copy.m_company;
        m_mail = copy.m_mail;
        m_facebook = copy.m_facebook;
        m_twitter = copy.m_twitter;
        m_color = copy.m_color;
        m_creationDate = copy.m_creationDate;
        m_deleteDate = copy.m_deleteDate;
        return *this;
    }

    int id() const
    {
        return m_id;
    }

    QString name() const
    {
        return m_name;
    }

    QString description() const
    {
        return m_description;
    }

    QString phone() const
    {
        return m_phone;
    }

    QString company() const
    {
        return m_company;
    }

    QString mail() const
    {
        return m_mail;
    }

    QString facebook() const
    {
        return m_facebook;
    }

    QString twitter() const
    {
        return m_twitter;
    }

    QColor color() const
    {
        return m_color;
    }

    QDate creationDate() const
    {
        return m_creationDate;
    }

    QDate deleteDate() const
    {
        return m_deleteDate;
    }

    QVariantList users() const
    {
        return m_users;
    }

    int numTaskOnGoing() const
    {
        return m_numTaskOnGoing;
    }

    int numTaskFinished() const
    {
        return m_numTaskFinished;
    }

    int numTaskTotal() const
    {
        return m_numTaskTotal;
    }

    int numBugTotal() const
    {
        return m_numBugTotal;
    }

    int numMessageTimeline() const
    {
        return m_numMessageTimeline;
    }

signals:

    void idChanged(int id);

    void nameChanged(QString name);

    void descriptionChanged(QString description);

    void phoneChanged(QString phone);

    void companyChanged(QString company);

    void mailChanged(QString mail);

    void facebookChanged(QString facebook);

    void twitterChanged(QString twitter);

    void colorChanged(QColor color);

    void creationDateChanged(QDate creationDate);

    void deleteDateChanged(QDate deleteDate);

    void usersChanged(QVariantList users);

    void numTaskOnGoingChanged(int numTaskOnGoing);

    void numTaskFinishedChanged(int numTaskFinished);

    void numTaskTotalChanged(int numTaskTotal);

    void numBugTotalChanged(int numBugTotal);

    void numMessageTimelineChanged(int numMessageTimeline);

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

    void setDescription(QString description)
    {
        if (m_description == description)
            return;

        m_description = description;
        emit descriptionChanged(description);
    }

    void setPhone(QString phone)
    {
        if (m_phone == phone)
            return;

        m_phone = phone;
        emit phoneChanged(phone);
    }

    void setCompany(QString company)
    {
        if (m_company == company)
            return;

        m_company = company;
        emit companyChanged(company);
    }

    void setMail(QString mail)
    {
        if (m_mail == mail)
            return;

        m_mail = mail;
        emit mailChanged(mail);
    }

    void setFacebook(QString facebook)
    {
        if (m_facebook == facebook)
            return;

        m_facebook = facebook;
        emit facebookChanged(facebook);
    }

    void setTwitter(QString twitter)
    {
        if (m_twitter == twitter)
            return;

        m_twitter = twitter;
        emit twitterChanged(twitter);
    }

    void setColor(QColor color)
    {
        if (m_color == color)
            return;

        m_color = color;
        emit colorChanged(color);
    }

    void setCreationDate(QDate creationDate)
    {
        if (m_creationDate == creationDate)
            return;

        m_creationDate = creationDate;
        emit creationDateChanged(creationDate);
    }

    void setDeleteDate(QDate deleteDate)
    {
        if (m_deleteDate == deleteDate)
            return;

        m_deleteDate = deleteDate;
        emit deleteDateChanged(deleteDate);
    }

    void setUsers(QVariantList users)
    {
        if (m_users == users)
            return;

        m_users = users;
        emit usersChanged(users);
    }

    void setNumTaskOnGoing(int numTaskOnGoing)
    {
        if (m_numTaskOnGoing == numTaskOnGoing)
            return;

        m_numTaskOnGoing = numTaskOnGoing;
        emit numTaskOnGoingChanged(numTaskOnGoing);
    }

    void setNumTaskFinished(int numTaskFinished)
    {
        if (m_numTaskFinished == numTaskFinished)
            return;

        m_numTaskFinished = numTaskFinished;
        emit numTaskFinishedChanged(numTaskFinished);
    }

    void setNumTaskTotal(int numTaskTotal)
    {
        if (m_numTaskTotal == numTaskTotal)
            return;

        m_numTaskTotal = numTaskTotal;
        emit numTaskTotalChanged(numTaskTotal);
    }

    void setNumBugTotal(int numBugTotal)
    {
        if (m_numBugTotal == numBugTotal)
            return;

        m_numBugTotal = numBugTotal;
        emit numBugTotalChanged(numBugTotal);
    }

    void setNumMessageTimeline(int numMessageTimeline)
    {
        if (m_numMessageTimeline == numMessageTimeline)
            return;

        m_numMessageTimeline = numMessageTimeline;
        emit numMessageTimelineChanged(numMessageTimeline);
    }

private:
    int m_id;
    QString m_name;
    QString m_description;
    QString m_phone;
    QString m_company;
    QString m_mail;
    QString m_facebook;
    QString m_twitter;
    QColor m_color;
    QDate m_creationDate;
    QDate m_deleteDate;
    QVariantList m_users;
    QStringList m_nameList;
    int m_numTaskOnGoing;
    int m_numTaskFinished;
    int m_numTaskTotal;
    int m_numBugTotal;
    int m_numMessageTimeline;
};

Q_DECLARE_METATYPE(ProjectData)
Q_DECLARE_METATYPE(ProjectData*)

#endif // PROJECTDATA

