#ifndef USERDATA_H
#define USERDATA_H

#include <QImage>
#include <QObject>
#include <QDate>

class UserData : public QObject
{
    Q_OBJECT
public:
    UserData();
    UserData(int id, QString firstName, QString lastName);

signals:

public slots:

private:
    int _Id;
    QString _FirstName;
    QString _LastName;
    QDate _Birthday;
    QImage _Avatar;
    QString _Email;
    QString _Phone;
    QString _Country;
    QString _LinkedIn;
    QString _Viadeo;
    QString _Twitter;
};

#endif // USERDATA_H
