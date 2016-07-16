#ifndef CREATENEWROLE_H
#define CREATENEWROLE_H

#include <QWidget>
#include <QPushButton>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QFormLayout>
#include <QCheckBox>
#include <QLineEdit>
#include <QLabel>
#include <QMap>

class CreateNewRole : public QWidget
{
    Q_OBJECT
public:
    explicit                    CreateNewRole(QWidget *parent = 0);
    const QMap<QString, bool>   GetRoleAuthorizations();
    const QString               GetRoleName();

signals:
    void        RoleConfirmed();

public slots:
    void        Open();

private slots:
    void        OkTriggered();

private:
    QVBoxLayout *_mainLayout;
    QHBoxLayout *_accessControlLayout;
    QFormLayout *_formCheckboxes;
    QFormLayout *_formCheckboxesMiddle;
    QFormLayout *_nameForm;
    QFormLayout *_okForm;

    QCheckBox   *_timelineTeam;
    QCheckBox   *_timelineCustomer;
    QCheckBox   *_gantt;
    QCheckBox   *_whiteboard;
    QCheckBox   *_bugtracker;
    QCheckBox   *_event;
    QCheckBox   *_task;
    QCheckBox   *_projectSettings;
    QCheckBox   *_cloud;
    QPushButton *_btnOK;
    QPushButton *_btnCancel;
    QLineEdit   *_name;

};

#endif // CREATENEWROLE_H
