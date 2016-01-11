#ifndef BODYBUGLIST_H
#define BODYBUGLIST_H

#include "BugTracker/IBugPage.h"
#include "BugTracker/BugListElement.h"
#include "BugTracker/BugListTitleWidget.h"
#include "BugTracker/BugEntity.h"
#include "SDataManager.h"
#include "IDataConnector.h"
#include <QWidget>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>
#include <QVBoxLayout>
#include <QScrollArea>
#include <QList>
#include <QPair>

#define UNUSED __attribute__((unused))
#define LIST_ELEM_HEIGHT    50
#define LIST_TITLE_HEIGHT   50

class BodyBugList : public QWidget, public IBugPage
{
    Q_OBJECT
public:
    explicit            BodyBugList(QWidget *parent = 0);
    virtual void        Show(BodyBugTracker *pageManager, QJsonObject *dataPage);
    virtual void        Hide();

private:
    void                DeleteListElements();
    void                CreateList();
    void                ClearLayout(QLayout *layout);

signals:
    void                OnLoadingDone(BodyBugTracker::BugTrackerPage page);

public slots: //Widget Slots
    void                TriggerNewIssue();
    void                TriggerFilterChange(BugListTitleWidget::BugState state);

public slots: //API Slots
    void                OnGetBugListSuccess(int id, QByteArray data);
    void                OnGetBugListClosedSuccess(int id, QByteArray data);
    void                OnRequestFailure(int id, QByteArray data);

private:
    BodyBugTracker      *_pageManager;
    int                 _bodyID;
    QVBoxLayout         *_mainLayout;
    QVBoxLayout         *_listAdapter;
    BugListTitleWidget  *_title;
    QScrollArea         *_listScrollView;
    QList<BugEntity>    _bugListOpen;
    QList<BugEntity>    _bugListClosed;
};

#endif // BODYBUGLIST_H
