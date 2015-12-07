#include "CustomerAccessSettings.h"

CustomerAccessSettings::CustomerAccessSettings(QString customerName, int customerId, int projectId, QString token, QWidget *parent) : _customerId(customerId), _projectId(projectId), QWidget(parent)
{
    _customerName = new QLabel(customerName);
    _deleteAccess = new QPushButton(tr("Delete"));
    _regenerate = new QPushButton(tr("Regenerate"));
    _mainLayout = new QHBoxLayout(this);
    _url = new QLineEdit(CUSTOMER_URL_BASE + token);
    _api = API::SDataManager::GetCurrentDataConnector();

    _url->setReadOnly(true);
    QObject::connect(_deleteAccess, SIGNAL(released()), this, SLOT(DeleteAccess()));
    QObject::connect(_regenerate, SIGNAL(released()), this, SLOT(Regenerate()));

    _mainLayout->addWidget(_customerName);
    _mainLayout->addWidget(_url);
    _mainLayout->addWidget(_regenerate);
    _mainLayout->addWidget(_deleteAccess);
}

void CustomerAccessSettings::Regenerate()
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(_customerName->text());
    _api->Post(API::DP_PROJECT, API::PR_CUSTOMER_GENERATE_ACCESS, data, this, "RegenerateSuccess", "Failure");
}

void CustomerAccessSettings::DeleteAccess()
{
    QVector<QString> data;

    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(QString::number(_projectId));
    data.append(QString::number(_customerId));
    _api->Delete(API::DP_PROJECT, API::DR_CUSTOMER_ACCESS, data, this, "DeleteAccessSuccess", "Failure");
}

void CustomerAccessSettings::Failure(int id, QByteArray data)
{
    QMessageBox::critical(this, "Internal Error", "Fail to retreive data from internet");
}

void CustomerAccessSettings::RegenerateSuccess(int id, QByteArray data)
{
    QMessageBox::information(this, tr("Generation successful"), tr("The customer access is successfuly generated"));
}

void CustomerAccessSettings::DeleteAccessSuccess(int id, QByteArray data)
{
    emit Deleted(this);
}
