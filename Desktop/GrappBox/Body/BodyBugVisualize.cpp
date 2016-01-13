#include "BodyBugVisualize.h"

BodyBugVisualize::BodyBugVisualize(QWidget *parent) : QWidget(parent)
{
    QString style;
    QWidget *widgetTitleCategory = new QWidget();
    QWidget *widgetTitleAssignee = new QWidget();
    QWidget *commentWidget = new QWidget();
    QLabel *lblTitleCategory = new QLabel("<h3>" + tr("Categories") + "</h3>");
    QLabel *lblTitleAssignee = new QLabel("<h3>" + tr("Assignee") + "</h3>");
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
    _titleBar = new BugViewTitleWidget("");
    _statusBar = new BugViewStatusBar();
    _categories = new BugViewCategoryWidget();
    _assignees = new BugViewAssigneeWidget();
    _commentArea = new QScrollArea();
    _commentLayout = new QVBoxLayout();
    _categoriesArea = new QScrollArea();
    _assigneesArea = new QScrollArea();

    _issueLayout->addWidget(_statusBar);
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

    _commentArea->setWidgetResizable(true);
    _commentArea->setWidget(new QWidget());
    _commentArea->widget()->setLayout(_commentLayout);

    _bodyLayout->addLayout(_issueLayout);
    _bodyLayout->addLayout(_sideMenuLayout);

    QObject::connect(_btnAssigneeAssign, SIGNAL(released()), this, SLOT(TriggerAssigneeBtnReleased()));
    QObject::connect(_btnCategoriesAssign, SIGNAL(released()), this, SLOT(TriggerCategoryBtnReleased()));
    QObject::connect(_titleBar, SIGNAL(OnIssueClosed(int)), this, SLOT(TriggerIssueClosed(int)));

    _mainLayout->addWidget(_titleBar);
    _mainLayout->addLayout(_bodyLayout);
    this->setLayout(_mainLayout);

    //Design
    _issueLayout->setObjectName("Issues");
    widgetTitleAssignee->setObjectName("Title");
    widgetTitleCategory->setObjectName("Title");
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

void BodyBugVisualize::Show(BodyBugTracker *pageManager, QJsonObject *data)
{
    QVector<QString> commentData, bugData;
    _bugId = (*data)["id"].toInt();
    _mainApp = pageManager;
    _titleBar->SetBugID(_bugId);
    _titleBar->SetTitle((*data)["title"].toString());
    this->DeleteComments();

    commentData.append(API::SDataManager::GetDataManager()->GetToken());
    commentData.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    commentData.append(QString::number(_bugId));

    bugData.append(API::SDataManager::GetDataManager()->GetToken());
    bugData.append(QString::number(_bugId));

    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_BUGCOMMENT, commentData, this, "TriggerGotComments", "TriggerAPIFailure");
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_BUG, bugData, this, "TriggerGotBug", "TriggerAPIFailure");



    //Start Fake Data
    QList<QJsonObject> fdataAssigneeView, fdataAssigneeAssign, fdataCategoryView, fdataCategoryAssign;
    QJsonObject obj1, obj2, obj3, obj4, obj5, obj6, obj7, obj8;

    obj1.insert("id", 1);
    obj1.insert("assigned", true);
    obj1.insert("name", "mougen_v");
    obj2.insert("id", 2);
    obj2.insert("assigned", true);
    obj2.insert("name", "launoi_a");


    obj5.insert("id", 4);
    obj5.insert("assigned", false);
    obj5.insert("name", "hemmer_r");
    obj6.insert("id", 3);
    obj6.insert("assigned", false);
    obj6.insert("name", "feytou_p");

    obj3.insert("id", 1);
    obj3.insert("assigned", false);
    obj3.insert("name", "question");
    obj7.insert("id", 2);
    obj7.insert("assigned", false);
    obj7.insert("name", "help wanted");

    obj4.insert("id", 3);
    obj4.insert("assigned", true);
    obj4.insert("name", "bug");
    obj8.insert("id", 4);
    obj8.insert("assigned", true);
    obj8.insert("name", "enhanced");

    fdataAssigneeView.append(obj1);
    fdataAssigneeView.append(obj2);
    fdataAssigneeAssign.append(obj5);
    fdataAssigneeAssign.append(obj1);
    fdataAssigneeAssign.append(obj2);
    fdataAssigneeAssign.append(obj6);

    fdataCategoryAssign.append(obj3);
    fdataCategoryAssign.append(obj7);
    fdataCategoryAssign.append(obj4);
    fdataCategoryAssign.append(obj8);
    fdataCategoryView.append(obj4);
    fdataCategoryView.append(obj8);
    //End Fake Data
    _categories->CreateAssignPageItems(fdataCategoryAssign);
    _categories->CreateViewPageItems(fdataCategoryView);

    emit OnLoadingDone(BodyBugTracker::BUGVIEW);
}

void BodyBugVisualize::Hide()
{
    _assignees->DeletePageItems(BugViewAssigneeWidget::BugAssigneePage::VIEW);
    _assignees->DeletePageItems(BugViewAssigneeWidget::BugAssigneePage::ASSIGN);
    _categories->DeletePageItems(BugViewCategoryWidget::BugCategoryPage::VIEW);
    _categories->DeletePageItems(BugViewCategoryWidget::BugCategoryPage::VIEW);
    hide();
}

void BodyBugVisualize::TriggerAssigneeBtnReleased()
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

void BodyBugVisualize::TriggerCategoryBtnReleased()
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

void BodyBugVisualize::DeleteComments()
{
    QLayoutItem *currentItem;
    BugViewPreviewWidget *commentWidget = new BugViewPreviewWidget(API::SDataManager::GetDataManager()->GetUserId(), true);

    while ((currentItem = _commentLayout->itemAt(0)) != nullptr)
    {
        if (currentItem->widget())
            currentItem->widget()->setParent(nullptr);
        _commentLayout->removeItem(currentItem);
    }
    QObject::connect(commentWidget, SIGNAL(OnCommented(BugViewPreviewWidget*)), this, SLOT(TriggerCommentButtonReleased(BugViewPreviewWidget*)));
    commentWidget->setFixedHeight(COMMENTBOX_HEIGHT);
    _commentLayout->addWidget(commentWidget);
}

void BodyBugVisualize::AddCommentsAtStart(const QList<QJsonObject> &comments)
{
    QList<QJsonObject>::const_iterator it;

    for (it = comments.begin(); it != comments.end(); ++it)
    {
        QJsonObject json = *it;
        BugViewPreviewWidget *newComment = new BugViewPreviewWidget(json[JSON_USERID].toInt());
        QImage img(QByteArray::fromBase64(json[JSON_AVATAR].toString().toStdString().c_str()), "PNG");
        QPixmap pix = QPixmap::fromImage(img);

        newComment->setFixedHeight(COMMENTBOX_HEIGHT);
        newComment->SetAvatar(pix);
        newComment->SetID(json[JSON_ID].toInt());
        newComment->SetDate(QDateTime::fromString(json[JSON_DATE].toString(), "yyyy-MM-dd hh:mm:ss"));
        newComment->SetCommentor(json[JSON_COMMENTOR].toString());
        newComment->SetComment(json[JSON_COMMENT].toString());
        newComment->SetCommentTitle(json[JSON_TITLE].toString());
        _commentLayout->insertWidget(_commentLayout->count() - 1, newComment);
    }

}

void BodyBugVisualize::TriggerIssueClosed(int UNUSED bugId)
{
    _mainApp->TriggerChangePage(BodyBugTracker::BUGLIST, nullptr);
}

void BodyBugVisualize::TriggerGotComments(int UNUSED id, QByteArray data)
{
    QJsonObject json = QJsonDocument::fromJson(data).object();
    QJsonArray comments = json["comments"].toArray();
    QJsonArray::iterator it;
    QList<QJsonObject> commentList;

    for (it = comments.begin(); it != comments.end(); ++it)
    {
        QJsonObject com = (*it).toObject();
        QDateTime editedAt = QDateTime::fromString(com["editedAt"].toObject()["date"].toString(), "yyyy-MM-dd HH:mm:ss.zzzz");
        QString date = editedAt.toString("yyyy-MM-dd hh:mm:ss");

        if (date == "")
        {
            editedAt = QDateTime::fromString(com["createdAt"].toObject()["date"].toString(), "yyyy-MM-dd HH:mm:ss.zzzz");
            date = editedAt.toString("yyyy-MM-dd HH:mm:ss");
        }
        com.insert(JSON_AVATAR, "");
        com.insert(JSON_COMMENTOR, QString::number(com["userId"].toInt()));
        com.insert(JSON_DATE, date);
        commentList.append(com);
    }
    this->AddCommentsAtStart(commentList);
}

void BodyBugVisualize::TriggerCommentButtonReleased(BugViewPreviewWidget *previewWidget)
{
    QVector<QString> commentData;
    QString comment = previewWidget->GetComment();
    QString commentTitle = previewWidget->GetCommentTitle();

    commentData.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    commentData.append(API::SDataManager::GetDataManager()->GetToken());
    commentData.append(commentTitle);
    commentData.append(comment);
    commentData.append(QString::number(_bugId));

    API::SDataManager::GetCurrentDataConnector()->Post(API::DP_BUGTRACKER, API::PR_COMMENT_BUG, commentData, this, "TriggerPushCommentSuccess", "TriggerAPIFailure");
    previewWidget->SetComment("");
    previewWidget->SetCommentTitle("");
}

void BodyBugVisualize::TriggerPushCommentSuccess(int UNUSED id, QByteArray data)
{
    QJsonObject com = QJsonDocument::fromJson(data).object()["comment"].toObject();
    QDateTime editedAt = QDateTime::fromString(com["editedAt"].toObject()["date"].toString(), "yyyy-MM-dd HH:mm:ss.zzzz");
    QString date = editedAt.toString("yyyy-MM-dd hh:mm:ss");
    QList<QJsonObject> comList;

    if (date == "")
    {
        editedAt = QDateTime::fromString(com["createdAt"].toObject()["date"].toString(), "yyyy-MM-dd HH:mm:ss.zzzz");
        date = editedAt.toString("yyyy-MM-dd HH:mm:ss");
    }
    com.insert(JSON_COMMENTOR, API::SDataManager::GetDataManager()->GetUserName() + " " + API::SDataManager::GetDataManager()->GetUserLastName());
    com.insert(JSON_DATE, date);
    comList.append(com);
    this->AddCommentsAtStart(comList);
}

void BodyBugVisualize::TriggerAPIFailure(int UNUSED id, QByteArray UNUSED data)
{
    QMessageBox::critical(this, tr("Connexion to Grappbox server failed"), tr("We can't contact the GrappBox server, check your internet connexion and retry. If the problem persist, please contact grappbox team at the address problem@grappbox.com"));
}

void BodyBugVisualize::TriggerGotBug(int UNUSED id, QByteArray data)
{
    QVector<QString> userProjectData;

    _bugData = QJsonDocument::fromJson(data).object()["ticket"].toObject();
    userProjectData.append(API::SDataManager::GetDataManager()->GetToken());
    userProjectData.append(QString::number(API::SDataManager::GetDataManager()->GetCurrentProject()));
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_BUGTRACKER, API::GR_PROJECT_USERS_ALL, userProjectData, this, "TriggerGotUserProject", "TriggerAPIFailure");
}

void BodyBugVisualize::TriggerGotUserProject(int UNUSED id, QByteArray data)
{

    QJsonObject json = QJsonDocument::fromJson(data).object();
    QJsonArray users = _bugData["users"].toArray();
    QList<QJsonObject> usersProjects;
    QList<QJsonObject> usersAssigned;


    for (QJsonArray::iterator it = users.begin(); it != users.end(); ++it)
    {
        QJsonObject currentBugUser = (*it).toObject();

        currentBugUser.insert("assigned", true);
        usersAssigned.append(currentBugUser);
    }
    for (int i = 1; json.contains("User " + QString::number(i)); ++i)
    {
        QJsonObject current = json["User " + QString::number(i)].toObject();
        bool assigned = false;

        for (QJsonArray::iterator it = users.begin(); it != users.end(); ++it)
        {
            QJsonObject currentBugUser = (*it).toObject();

            if (currentBugUser["id"].toInt() == current["id"].toInt())
            {
                assigned = true;
                break;
            }
        }
        current.insert("assigned", assigned);
        usersProjects.append(current);
    }
    _assignees->CreateViewPageItems(usersAssigned);
    _assignees->CreateAssignPageItems(usersProjects);
    emit OnLoadingDone(BodyBugTracker::BUGVIEW);
}
