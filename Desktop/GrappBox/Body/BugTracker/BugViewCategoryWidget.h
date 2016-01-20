#ifndef BUGVIEWCATEGORYWIDGET_H
#define BUGVIEWCATEGORYWIDGET_H

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
#include <QMessageBox>
#include <QMap>
#include <QDebug>

#define UNUSED __attribute__((unused))
#define ITEM_ID         "id"
#define ITEM_ASSIGNED   "assigned"
#define ITEM_NAME       "name"

class BugViewCategoryWidget : public QWidget
{
    Q_OBJECT

public:
    enum BugCategoryPage
    {
        VIEW,
        ASSIGN
    };

public:
    explicit            BugViewCategoryWidget(int bugId = -1, QWidget *parent = 0);
    void                DeletePageItems(const BugViewCategoryWidget::BugCategoryPage page);
    void                CreateViewPageItems(const QList<QJsonObject> &items);
    void                CreateAssignPageItems(const QList<QJsonObject> &items);
    BugCategoryPage     GetCurrentPage();
    void                DisableAPIAssignation(const bool disable);
    const QList<int>    GetAllAssignee() const;
    void                SetBugId(const int bugId);

signals: //Common signals
    void            OnPageChanged(BugCategoryPage);
    void            OnPageItemsCreated(BugViewCategoryWidget::BugCategoryPage);
    void            OnPageItemsDeleted(BugCategoryPage);

signals: //Assign page signals
    void            OnCreated(int);
    void            OnAssigned(int, QString);
    void            OnDelAssigned(int, QString);

signals: //View page signals

public slots: //Common slots
    void            TriggerOpenPage(const BugCategoryPage page);

public slots: //Assign page slots
    void            TriggerCreateReleased();
    void            TriggerCheckChange(bool checked, int id, QString name);
    void            TriggerCreateSuccess(int id, QByteArray data);
    void            TriggerAPIFailure(int id, QByteArray data);
    void            TriggerAssignSuccess(int id, QByteArray data);
    void            TriggerAssignFailure(int id, QByteArray data);
    void            TriggerUnAssignSuccess(int id, QByteArray data);
    void            TriggerUnAssignFailure(int id, QByteArray data);

public slots: //View page slots

private:
    BugCheckableLabel   *SearchCheckbox(int id);
    QLabel              *SearchLabel(int id);

private:
    int             _bugId;
    QWidget         *_viewPage;
    QVBoxLayout     *_mainViewLayout;
    QWidget         *_assignPage;
    QVBoxLayout     *_mainAssignLayout;
    QStackedLayout  *_mainWidget;
    QLineEdit       *_creationCategory;
    QPushButton     *_creationBtn;
    QMap<int, int>  _apiAssignationWait;
    bool            _isAPIAssignActivated;
};

#endif // BUGVIEWCATEGORYWIDGET_H
