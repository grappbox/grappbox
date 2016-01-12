#include "BodyBugCreation.h"

BodyBugCreation::BodyBugCreation(QWidget *parent) : QWidget(parent)
{
    QString style;
    QWidget *widgetTitleCategory = new QWidget();
    QWidget *widgetTitleAssignee = new QWidget();
    QLabel *lblTitleCategory = new QLabel(tr("Categories"));
    QLabel *lblTitleAssignee = new QLabel(tr("Assignee"));
    QHBoxLayout *layoutTitleCategory = new QHBoxLayout();
    QHBoxLayout *layoutTitleAssignee = new QHBoxLayout();
    QVBoxLayout *layoutCategoryArea = new QVBoxLayout();
    QVBoxLayout *layoutAssigneeArea = new QVBoxLayout();
    _btnCategoriesAssign = new QPushButton(PH_ASSIGNATION);
    _btnAssigneeAssign = new QPushButton(PH_ASSIGNATION);
    _bugId = -1;
    _mainLayout = new QVBoxLayout();
    _bodyLayout = new QHBoxLayout();
    _issueLayout = new QVBoxLayout();
    _sideMenuLayout = new QVBoxLayout();
    _titleBar = new BugViewTitleWidget(tr("Enter the issue name here..."), true);
    _categories = new BugViewCategoryWidget();
    _assignees = new BugViewAssigneeWidget();
    _commentArea = new QScrollArea();
    _commentLayout = new QVBoxLayout();
    _categoriesArea = new QScrollArea();
    _assigneesArea = new QScrollArea();
    _waitingPageCreated = 0;

    _issueLayout->addWidget(_commentArea);

    widgetTitleCategory->setLayout(layoutTitleCategory);
    layoutTitleCategory->addWidget(lblTitleCategory);
    layoutTitleCategory->addWidget(_btnCategoriesAssign);

    widgetTitleAssignee->setLayout(layoutTitleAssignee);
    layoutTitleAssignee->addWidget(lblTitleAssignee);
    layoutTitleAssignee->addWidget(_btnAssigneeAssign);

    _categoriesArea->setMaximumWidth(250);
    _assigneesArea->setMaximumWidth(250);

    layoutAssigneeArea->addWidget(_assignees);
    layoutCategoryArea->addWidget(_categories);

    _categoriesArea->setLayout(layoutCategoryArea);
    _assigneesArea->setLayout(layoutAssigneeArea);

    _sideMenuLayout->addWidget(widgetTitleCategory);
    _sideMenuLayout->addWidget(_categoriesArea);
    _sideMenuLayout->addWidget(widgetTitleAssignee);
    _sideMenuLayout->addWidget(_assigneesArea);

    _commentArea->setLayout(_commentLayout);

    _bodyLayout->addLayout(_issueLayout);
    _bodyLayout->addLayout(_sideMenuLayout);

    QObject::connect(_btnAssigneeAssign, SIGNAL(released()), this, SLOT(TriggerAssigneeBtnReleased()));
    QObject::connect(_btnCategoriesAssign, SIGNAL(released()), this, SLOT(TriggerCategoryBtnReleased()));
    QObject::connect(_assignees, SIGNAL(OnPageItemsCreated(BugViewAssigneeWidget::BugAssigneePage)), this, SLOT(TriggerAssigneePageCreated(BugViewAssigneeWidget::BugAssigneePage)));
    QObject::connect(_categories, SIGNAL(OnPageItemsCreated(BugViewCategoryWidget::BugCategoryPage)), this, SLOT(TriggerCategoryPageCreated(BugViewCategoryWidget::BugCategoryPage)));

    _mainLayout->addWidget(_titleBar);
    _mainLayout->addLayout(_bodyLayout);
    this->setLayout(_mainLayout);

    _btnCategoriesAssign->hide();
    _btnAssigneeAssign->hide();
    _btnCategoriesAssign->setEnabled(false);
    _btnAssigneeAssign->setEnabled(false);
    _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
    _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
    _categories->DisableAPIAssignation(true);
    _assignees->DisableAPIAssignation(true);

    //Design
    style = "QPushButton{"
            "background-color: #595959;"
            "color : #ffffff;"
            "border-radius: 2px;"
            "min-width : 80px;"
            "min-height : 25px;"
            "max-width : 80px;"
            "max-height : 25px;"
            "font-size: 12px;"
            "font-weight: bold;"
            "}"
            "QPushButton:hover{"
            "background-color: #bababa;"
            "}"
            "QScrollArea{"
            "border: 0px;"
            "background-color: #FFFFFF;"
            "}"
            "QWidget#Title{"
            "border-bottom: 1px solid #000000;"
            "}"
            "QVBoxLayout#Issues{"
            "alternate-background-color: #a6a6a6;"
            "background: #d9d9d9;"
            "}";
    this->setStyleSheet(style);
}

void BodyBugCreation::Show(BodyBugTracker *pageManager, QJsonObject UNUSED *data)
{
    QVector<QString> tagsAndUsersData;
    QString token;
    int currentProject;

    _bugId = -1;
    _mainApp = pageManager;
    _titleBar->SetBugID(_bugId);
    _titleBar->SetTitle(tr("Enter the issue name here..."));
    this->DeleteComments();

    token = API::SDataManager::GetDataManager()->GetToken();
    currentProject = API::SDataManager::GetDataManager()->GetCurrentProject();
    tagsAndUsersData.append(token);
    tagsAndUsersData.append(QString::number(currentProject));
    _waitingPageCreated = 2;

    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_PROJECT_USERS_ALL, tagsAndUsersData, this, "TriggerGotProjectUsers", "TriggerAPIFailure");
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_PROJECTBUGTAG_ALL, tagsAndUsersData, this, "TriggerGotProjectTags", "TriggerAPIFailure");
}

void BodyBugCreation::Hide()
{
    _assignees->DeletePageItems(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
    _categories->DeletePageItems(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
    hide();
}

void BodyBugCreation::TriggerAssigneeBtnReleased()
{
    if (_assignees->GetCurrentPage() == BugViewAssigneeWidget::BugAssigneePage::VIEW)
    {
        _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
        _btnAssigneeAssign->setText(PH_BACK);
    }
    else
    {
        _assignees->TriggerOpenPage(BugViewAssigneeWidget::BugAssigneePage::VIEW);
        _btnAssigneeAssign->setText(PH_ASSIGNATION);
    }
}

void BodyBugCreation::TriggerCategoryBtnReleased()
{
    if (_categories->GetCurrentPage() == BugViewCategoryWidget::BugCategoryPage::VIEW)
    {
        _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::ASSIGN);
        _btnCategoriesAssign->setText(PH_BACK);
    }
    else
    {
        _categories->TriggerOpenPage(BugViewCategoryWidget::BugCategoryPage::VIEW);
        _btnCategoriesAssign->setText(PH_ASSIGNATION);
    }
}

void BodyBugCreation::DeleteComments()
{
    QLayoutItem *currentItem;
    BugViewPreviewWidget *commentWidget = new BugViewPreviewWidget(true, true);

    while ((currentItem = _commentLayout->itemAt(0)) != nullptr)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(nullptr);
        _commentLayout->removeItem(currentItem);
    }
    commentWidget->setFixedHeight(COMMENTBOX_HEIGHT);
    _commentLayout->addWidget(commentWidget);
    _commentLayout->setAlignment(commentWidget, Qt::AlignTop);
    _commentWidget = commentWidget;
    QObject::connect(_commentWidget, SIGNAL(OnCommented()), this, SLOT(TriggerComment()));
}

void BodyBugCreation::TriggerComment()
{
    QVector<QString> data;
    QString comment = _commentWidget->GetComment();

    data.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    data.append(API::SDataManager::GetDataManager()->GetToken());
    data.append(_titleBar->GetTitle());
    data.append(comment);
    data.append(QString::number(BUGSTATE_OPEN));
    data.append("");

    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_BUGTRACKER, API::PR_CREATE_BUG, data, this, "TriggerBugCreated", "TriggerAPIFailure");
}

void BodyBugCreation::TriggerBugCreated(int UNUSED id, QByteArray data)
{
    QVector<QString> commentData;
    QJsonObject json = QJsonDocument::fromJson(data).object();
    QString comment = _commentWidget->GetComment();
    QString commentTitle = _commentWidget->GetCommentTitle();
    QList<int> assignedUser = _assignees->GetAllAssignee();
    QList<int> assignedCategories = _categories->GetAllAssignee();
    QList<int>::iterator it;
    QVector<QString> tagData;
    int bugID = json["ticket"].toObject()["id"].toInt();
    _waitingPageCreated = -1;
    _bugId = bugID;

    for (it = assignedCategories.begin(); it != assignedCategories.end(); ++it)
    {
        tagData.clear();
        tagData.append(API::SDataManager::GetDataManager()->GetToken());
        tagData.append(QString::number(bugID));
        tagData.append(QString::number(*it));
        API::SDataManager::GetCurrentDataConnector()->Put(API::DP_BUGTRACKER, API::PUTR_ASSIGNTAG, tagData, this, "DoNothing", "TriggerAPIFailure");
    }
    tagData.clear();
    tagData.append(QString::number(bugID));
    tagData.append(API::SDataManager::GetDataManager()->GetToken());
    for (it = assignedUser.begin(); it != assignedUser.end(); ++it)
    {
        tagData.append(QString::number((*it)));
    }
    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_BUGTRACKER, API::PR_ASSIGNUSER_BUG, tagData, this, "DoNothing", "TriggerAPIFailure");

    commentData.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    commentData.append(API::SDataManager::GetDataManager()->GetToken());
    commentData.append(comment);
    commentData.append(commentTitle);
    commentData.append(QString::number(json["ticket"].toObject()["id"].toInt()));

    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_BUGTRACKER, API::PR_COMMENT_BUG, commentData, this, "TriggerBugCommented", "TriggerAPIFailure");
}

void BodyBugCreation::TriggerGotProjectTags(int UNUSED id, QByteArray data)
{
    QList<QJsonObject> categoriesObjects;
    QJsonObject json = QJsonDocument::fromJson(data).object();

    for (int i = 1; json.contains("Tag " + QString::number(i)); ++i)
    {
        QJsonObject current = json["Tag " + QString::number(i)].toObject();

        current.insert("assigned", false);
        categoriesObjects.append(current);
    }
    _categories->CreateAssignPageItems(categoriesObjects);
}

void BodyBugCreation::TriggerGotProjectUsers(int UNUSED id, QByteArray data)
{
    QList<QJsonObject> usersObjects;
    QJsonObject json = QJsonDocument::fromJson(data).object();

    for (int i = 1; json.contains("User " + QString::number(i)); ++i)
    {
        QJsonObject current = json["User " + QString::number(i)].toObject();

        current.insert("assigned", false);
        usersObjects.append(current);
    }
    _assignees->CreateAssignPageItems(usersObjects);
}

void BodyBugCreation::TriggerBugCommented(int UNUSED id, QByteArray UNUSED data)
{
    QJsonObject *intent = new QJsonObject();


    intent->insert("id", _bugId);
    intent->insert("title", this->_titleBar->GetTitle());
    _mainApp->TriggerChangePage(BodyBugTracker::BugTrackerPage::BUGVIEW, intent);
}

void BodyBugCreation::TriggerAPIFailure(int UNUSED id, QByteArray UNUSED data)
{
    --_waitingPageCreated;
    if (_waitingPageCreated == 0)
        emit OnLoadingDone(BodyBugTracker::BUGCREATE);
    QMessageBox::critical(this, tr("Connexion to Grappbox server failed"), tr("We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com"));
}

void BodyBugCreation::TriggerAssigneePageCreated(BugViewAssigneeWidget::BugAssigneePage page)
{
    --_waitingPageCreated;
    if (_waitingPageCreated <= 0)
    {
        _waitingPageCreated = 0;
        emit OnLoadingDone(BodyBugTracker::BUGCREATE);
    }
}

void BodyBugCreation::TriggerCategoryPageCreated(BugViewCategoryWidget::BugCategoryPage page)
{
    --_waitingPageCreated;
    if (_waitingPageCreated <= 0)
    {
        _waitingPageCreated = 0;
        emit OnLoadingDone(BodyBugTracker::BUGCREATE);
    }
}

void BodyBugCreation::DoNothing(int UNUSED id, QByteArray UNUSED data){}
