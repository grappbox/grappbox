#ifndef CREATENEWCUSTOMERACCESSWINDOW_H
#define CREATENEWCUSTOMERACCESSWINDOW_H

#include <QWidget>
#include <QPushButton>
#include <QLineEdit>
#include <QFormLayout>
#include <QLabel>

#define PH_CUSTOMER_ACCESS_NAME tr("Enter access name here...")

class CreateNewCustomerAccessWindow : public QWidget
{
    Q_OBJECT
public:
    explicit CreateNewCustomerAccessWindow(QWidget *parent = 0);

signals:
    void        CustomerCreationProcessEnd(QString);

public slots:
    void        Open();
    void        OKTriggered();

private:
    QFormLayout *_mainLayout;
    QPushButton *_OK;
    QPushButton *_cancel;
    QLineEdit   *_customerName;
};

#endif // CREATENEWCUSTOMERACCESSWINDOW_H
