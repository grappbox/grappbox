#include "InviteUserWindow.h"

InviteUserWindow::InviteUserWindow(QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QFormLayout(this);
    _mail = new QLineEdit(PH_INVITE_MAIL);
    _ok = new QPushButton(tr("OK"));
    _cancel = new QPushButton(tr("Cancel"));

    _mainLayout->addWidget(new QLabel(tr("<h2>Invite a user in the project</h2>")));
    _mainLayout->addRow(new QLabel(tr("Enter E-mail : ")), _mail);
    _mainLayout->addRow(_ok, _cancel);

    QObject::connect(_cancel, SIGNAL(released()), this, SLOT(hide()));
    QObject::connect(_ok, SIGNAL(released()), this, SLOT(OkTriggered()));

    this->setLayout(_mainLayout);
}

void InviteUserWindow::OkTriggered()
{
    emit InviteUserCompleted((_mail->text() != PH_INVITE_MAIL) ? _mail->text() : "");
    this->hide();
}

void InviteUserWindow::Open()
{
    _mail->setText(PH_INVITE_MAIL);
    this->show();
}
