#include "CreateNewCustomerAccessWindow.h"

CreateNewCustomerAccessWindow::CreateNewCustomerAccessWindow(QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QFormLayout(this);
    _OK = new QPushButton(tr("OK"));
    _cancel = new QPushButton(tr("cancel"));
    _customerName = new QLineEdit(PH_CUSTOMER_ACCESS_NAME);

    QObject::connect(_OK, SIGNAL(released()), this, SLOT(OKTriggered()));
    QObject::connect(_cancel, SIGNAL(released()), this, SLOT(hide()));

    _mainLayout->addWidget(new QLabel(tr("<h2>Create a new customer access</h2>")));
    _mainLayout->addRow(new QLabel(tr("Access name : ")), _customerName);
    _mainLayout->addRow(_OK, _cancel);

}

void CreateNewCustomerAccessWindow::Open()
{
    _customerName->setText(PH_CUSTOMER_ACCESS_NAME);
    show();
}

void CreateNewCustomerAccessWindow::OKTriggered()
{
    emit CustomerCreationProcessEnd(_customerName->text() != PH_CUSTOMER_ACCESS_NAME ? _customerName->text() : "");
    hide();
}
