#ifndef CUSTOMERACCESSSETTINGS_H
#define CUSTOMERACCESSSETTINGS_H

#include <QWidget>
#include <QPushButton>
#include <QHBoxLayout>
#include <QLabel>
#include <QMessageBox>
#include <QLineEdit>
#include "SDataManager.h"
#include "IDataConnector.h"

#define CUSTOMER_URL_BASE QString("http://www.grappbox.com/app/")

class CustomerAccessSettings : public QWidget
{
    Q_OBJECT
public:
    explicit CustomerAccessSettings(QString customerName, int customerId, int projectId, QString token, QWidget *parent = 0);

signals:
    void        Deleted(CustomerAccessSettings*);

public slots:
    void        Failure(int id, QByteArray data);
    void        Regenerate();
    void        DeleteAccess();
    void        RegenerateSuccess(int id, QByteArray data);
    void        DeleteAccessSuccess(int id, QByteArray data);

private:
    QLabel              *_customerName;
    QLineEdit           *_url;
    int                 _customerId;
    int                 _projectId;
    QPushButton         *_deleteAccess;
    QPushButton         *_regenerate;
    QHBoxLayout         *_mainLayout;
    API::IDataConnector *_api;

};

#endif // CUSTOMERACCESSSETTINGS_H
