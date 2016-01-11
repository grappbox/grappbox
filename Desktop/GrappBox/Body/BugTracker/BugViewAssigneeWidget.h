#ifndef BUGVIEWASSIGNEEWIDGET_H
#define BUGVIEWASSIGNEEWIDGET_H

#include "BugTracker/BugCheckableLabel.h"
#include <QWidget>
#include <QStackedWidget>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QLabel>
#include <QCheckBox>
#include <QList>
#include <QJsonObject>
#include <QPushButton>
#include <QLineEdit>

#define ITEM_ID         "id"
#define ITEM_ASSIGNED   "assigned"
#define ITEM_FIRSTNAME  "first_name"
#define ITEM_LASTNAME   "last_name"

class BugViewAssigneeWidget : public QWidget
{
    Q_OBJECT

public:
    enum BugAssigneePage
    {
        VIEW,
        ASSIGN
    };

public:
    explicit            BugViewAssigneeWidget(QWidget *parent = 0);
    void                DeletePageItems(const BugViewAssigneeWidget::BugAssigneePage page);
    void                CreateViewPageItems(const QList<QJsonObject> &items);
    void                CreateAssignPageItems(const QList<QJsonObject> &items);
    BugAssigneePage     GetCurrentPage() const;
    void                DisableAPIAssignation(const bool disable);
    const QList<int>    GetAllAssignee() const;

signals: //Common signals
    void            OnPageChanged(BugAssigneePage);
    void            OnPageItemsCreated(BugViewAssigneeWidget::BugAssigneePage);
    void            OnPageItemsDeleted(BugAssigneePage);

signals: //Assign page slots
    void            OnCreated(int);
    void            OnAssigned(int, QString);
    void            OnDelAssigned(int, QString);

signals: //View page slots

public slots: //Common slots
    void            TriggerOpenPage(const BugAssigneePage page);

public slots: //Assign page slots
    void            TriggerCreateReleased();
    void            TriggerCheckChange(bool checked, int id, QString name);

public slots: //View page slots

private:
    QWidget         *_viewPage;
    QVBoxLayout     *_mainViewLayout;
    QWidget         *_assignPage;
    QVBoxLayout     *_mainAssignLayout;
    QStackedWidget  *_mainWidget;
    QLineEdit       *_creationCategory;
    QPushButton     *_creationBtn;
     bool            _isAPIAssignActivated;
};

#endif // BUGVIEWASSIGNEEWIDGET_H
