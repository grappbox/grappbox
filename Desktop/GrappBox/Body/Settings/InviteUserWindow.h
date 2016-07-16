#ifndef INVITEUSERWINDOW_H
#define INVITEUSERWINDOW_H

#include <QWidget>
#include <QFormLayout>
#include <QPushButton>
#include <QLineEdit>
#include <QLabel>

#define PH_INVITE_MAIL tr("john@example.com")

class InviteUserWindow : public QWidget
{
    Q_OBJECT
public:
    explicit InviteUserWindow(QWidget *parent = 0);

signals:
    void InviteUserCompleted(QString);

public slots:
    void OkTriggered();
    void Open();

private:
    QFormLayout *_mainLayout;
    QLineEdit   *_mail;
    QPushButton *_ok;
    QPushButton *_cancel;
};

#endif // INVITEUSERWINDOW_H
