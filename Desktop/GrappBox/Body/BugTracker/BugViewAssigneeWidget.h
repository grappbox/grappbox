#ifndef BUGVIEWASSIGNEEWIDGET_H
#define BUGVIEWASSIGNEEWIDGET_H

#include "BugTracker/BugCheckableLabel.h"
#include "SDataManager.h"
#include <QWidget>
#include <QStackedLayout>
#include <QVBoxLayout>
#include <QHBoxLayout>
#include <QLabel>
#include <QCheckBox>
#include <QList>
#include <QJsonObject>
#include <QJsonDocument>
#include <QPushButton>
#include <QLineEdit>
#include <QDebug>
#include <QMessageBox>
#include <QMap>

#define UNUSED __attribute__((unused))
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
    explicit            BugViewAssigneeWidget(int bugId = -1, QWidget *parent = 0);
    void                DeletePageItems(const BugViewAssigneeWidget::BugAssigneePage page);
    void                CreateViewPageItems(const QList<QJsonObject> &items);
    void                CreateAssignPageItems(const QList<QJsonObject> &items);
    BugAssigneePage     GetCurrentPage() const;
    void                DisableAPIAssignation(const bool disable);
    const QList<int>    GetAllAssignee() const;
    void                SetBugId(int bugId);

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
    void            TriggerCheckChange(bool checked, int id, QString name);
    void            TriggerAPIFailure(int id, QByteArray data);
    void            TriggerAssignFailure(int id, QByteArray data);
    void            TriggerUnAssignFailure(int id, QByteArray data);
    void            TriggerAssignSuccess(int id, QByteArray data);
    void            TriggerUnAssignSuccess(int id, QByteArray data);

public slots: //View page slots

private:
    QLabel                  *SearchLabel(int id);
    BugCheckableLabel       *SearchCheckbox(int id);

private:
    int             _bugId;
    QWidget         *_viewPage;
    QVBoxLayout     *_mainViewLayout;
    QWidget         *_assignPage;
    QVBoxLayout     *_mainAssignLayout;
    QStackedLayout  *_mainWidget;
    bool            _isAPIAssignActivated;
    QMap<int, int>  _apiAssignWaiting;
};

#endif // BUGVIEWASSIGNEEWIDGET_H
