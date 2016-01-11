#include <QtWidgets/QMessageBox>
#include <QException>
#include <QtNetwork/QNetworkRequest>
#include "DataConnectorOnline.h"
#include <QDebug>

using namespace API;

DataConnectorOnline::DataConnectorOnline()
{
    _Manager = new QNetworkAccessManager();
}

void DataConnectorOnline::OnResponseAPI()
{
    QNetworkReply *request = dynamic_cast<QNetworkReply*>(QObject::sender());
    qDebug() << request->request().url().toString();
    if (request == NULL || !_Request.contains(request))
    {
        QMessageBox::critical(NULL, "Critical error", "Unable to cast the reply of the API response.", QMessageBox::Ok);
    }
    QByteArray req = request->readAll();
    if (request->error())
    {
        qDebug() << request->errorString();
        qDebug() << request->error();
        QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotFailure, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
    }
    else
        QMetaObject::invokeMethod(_CallBack[request]._Request, _CallBack[request]._SlotSuccess, Q_ARG(int, _Request[request]), Q_ARG(QByteArray, req));
}

int DataConnectorOnline::Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case PR_LOGIN:
        reply = Login(data);
        break;
    case PR_ROLE_ADD:
        reply = AddRole(data);
        break;
    case PR_ROLE_ASSIGN:
        reply = AttachRole(data);
        break;
    case PR_CUSTOMER_GENERATE_ACCESS:
        reply = CustomerGenerateAccess(data);
        break;
    case PR_CREATE_BUG:
        reply = OpenBug(data);
        break;
    case PR_COMMENT_BUG:
        reply = CommentBug(data);
        break;
    }
    if (reply == NULL)
        throw QException();
    _CallBack[reply] = DataConnectorCallback();
    _CallBack[reply]._Request = requestResponseObject;
    _CallBack[reply]._SlotFailure = slotFailure;
    _CallBack[reply]._SlotSuccess = slotSuccess;
    int maxInt = 1;
    for (QMap<QNetworkReply*,int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
    {
        if (it.value() >= maxInt)
        {
            maxInt = it.value() + 1;
        }
    }
    _Request[reply] = maxInt;
    return maxInt;
}

int DataConnectorOnline::Put(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char *slotSuccess, const char *slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case PUTR_UserSettings:
        reply = PutUserSettings(data);
        break;
    case PUTR_ProjectSettings:
        reply = PutProjectSettings(data);
        break;
    case PUTR_INVITE_USER:
        reply = ProjectInvite(data);
        break;
    case PUTR_ASSIGNTAG:
        reply = AssignTagToBug(data);
        break;
    }
    if (reply == NULL)
        throw QException();
    _CallBack[reply] = DataConnectorCallback();
    _CallBack[reply]._Request = requestResponseObject;
    _CallBack[reply]._SlotFailure = slotFailure;
    _CallBack[reply]._SlotSuccess = slotSuccess;
    int maxInt = 1;
    for (QMap<QNetworkReply*,int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
    {
        if (it.value() >= maxInt)
        {
            maxInt = it.value() + 1;
        }
    }
    _Request[reply] = maxInt;
    return maxInt;
}

int DataConnectorOnline::Get(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case GR_LOGOUT:
        reply = Logout(data);
        break;

    case GR_LIST_PROJECT:
        reply = GetAction("dashboard/getprojectsglobalprogress", data);
        break;

    case GR_PROJECT:
        reply = GetAction("dashboard/getprojectbasicinformations", data);
        break;

    case GR_CREATOR_PROJECT:
        reply = GetAction("dashboard/getprojectcreator", data);
        break;

    case GR_LIST_MEMBER_PROJECT:
        reply = GetAction("dashboard/getteamoccupation", data);
        break;

    case GR_LIST_MEETING:
        reply = GetAction("dashboard/getnextmeetings", data);
        break;
    case GR_USER_SETTINGS:
        reply = GetAction("user/basicinformations", data);
        break;
    case GR_PROJECTS_USER:
        reply = GetAction("user/getprojects", data);
        break;
    case GR_PROJECT_ROLE:
        reply = GetAction("roles/getprojectroles", data);
        break;
    case GR_PROJECT_USERS:
        reply = GetAction("dashboard/getprojectpersons", data);
        break;
    case GR_PROJECT_CANCEL_DELETE:
        reply = GetAction("projects/retrieveproject", data);
        break;
    case GR_PROJECT_USER_ROLE:
        reply = GetAction("roles/getrolebyprojectanduser", data);
        break;
    case GR_CUSTOMER_ACCESSES:
        reply = GetAction("projects/getcustomeraccessbyproject", data);
        break;
    case GR_CUSTOMER_ACCESS_BY_ID:
        reply = GetAction("projects/getcustomeraccessbyid", data);
        break;
    case GR_USERPROJECT_BUG:
        reply = GetAction("bugtracker/getticketsbyuser", data);
        break;
    case GR_XLAST_BUG_OFFSET:
        reply = GetAction("bugtracker/getlasttickets", data);
        break;
    case GR_XLAST_BUG_OFFSET_BY_STATE:
        reply = GetAction("bugtracker/getticketsbystate", data);
        break;
    case GR_XLAST_BUG_OFFSET_CLOSED:
        reply = GetAction("bugtracker/getlastclosedtickets", data);
        break;
    case GR_PROJECTBUG_ALL:
        reply = GetAction("bugtracker/gettickets", data);
        break;
    case GR_BUGCOMMENT:
        reply = GetAction("bugtracker/getcomments", data);
        break;
    case GR_GETBUGS_STATUS:
        reply = GetAction("bugtracker/getStates", data);
    case GR_LIST_TIMELINE:
        reply = GetAction("timeline/gettimelines", data);
        break;
    case GR_TIMELINE:
        reply = GetAction("timeline/getlastmessages", data);
        break;
    case GR_COMMENT_TIMELINE:
        reply = GetAction("timeline/getcomments", data);
        break;
    case GR_PROJECTBUGTAG_ALL:
        reply = GetAction("bugtracker/getprojecttags", data);
        break;
    case GR_PROJECT_USERS_ALL:
        reply = GetAction("projects/getusertoproject", data);
        break;
    }
    if (reply == NULL)
        throw QException();
    _CallBack[reply] = DataConnectorCallback();
    _CallBack[reply]._Request = requestResponseObject;
    _CallBack[reply]._SlotFailure = slotFailure;
    _CallBack[reply]._SlotSuccess = slotSuccess;
    int maxInt = 1;
    for (QMap<QNetworkReply*,int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
    {
        if (it.value() >= maxInt)
        {
            maxInt = it.value() + 1;
        }
    }
    _Request[reply] = maxInt;
    return maxInt;
}

int DataConnectorOnline::Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure)
{
    QNetworkReply *reply = NULL;
    switch (request)
    {
    case DR_PROJECT_ROLE:
        reply = DeleteProjectRole(data);
        break;
    case DR_ROLE_DETACH:
        reply = DetachRole(data);
        break;
    case DR_PROJECT_USER:
        reply = DeleteProjectUser(data);
        break;
    case DR_PROJECT:
        reply = DeleteProject(data);
        break;
    case DR_CUSTOMER_ACCESS:
        reply = DeleteCustomerAccess(data);
        break;
    }
    if (reply == NULL)
        throw QException();
    _CallBack[reply] = DataConnectorCallback();
    _CallBack[reply]._Request = requestResponseObject;
    _CallBack[reply]._SlotFailure = slotFailure;
    _CallBack[reply]._SlotSuccess = slotSuccess;
    int maxInt = 1;
    for (QMap<QNetworkReply*,int>::const_iterator it = _Request.constBegin(); it != _Request.constEnd(); ++it)
    {
        if (it.value() >= maxInt)
        {
            maxInt = it.value() + 1;
        }
    }
    _Request[reply] = maxInt;
    return maxInt;
}

QNetworkReply *DataConnectorOnline::Login(QVector<QString> &data)
{
    QJsonObject json;
    json["login"] = data[0];
    json["password"] = data[1];
    QJsonDocument doc(json);
    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);

    QNetworkRequest requestSend(QUrl(URL_API + QString("accountadministration/login")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->post(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::PutUserSettings(QVector<QString> &data)
{
    QJsonObject json;
    QJsonObject birthdayJson;
    QTimeZone timezone;

    if (data[0] != "")
        json["first_name"] = data[0];
    if (data[1] != "")
        json["last_name"] = data[1];
    if (data[2] != "")
    {
        birthdayJson["date"] = data[2];
        birthdayJson["timezone_type"] = "3";
        birthdayJson["timezone"] = timezone.displayName(QTimeZone::StandardTime);
        json["birthday"] = birthdayJson;
    }

    if (data[3] != "")
        json["avatar"] = data[3];
    if (data[4] != "")
        json["phone"] = data[4];
    if (data[5] != "")
        json["country"] = data[5];
    if (data[6] != "")
        json["linkedin"] = data[6];
    if (data[7] != "")
        json["viadeo"] = data[7];
    if (data[8] != "")
        json["twitter"] = data[8];
    if (data[9] != "")
        json["password"] = data[9];

    QJsonDocument doc(json);
    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
    QNetworkRequest requestSend(QUrl(URL_API + QString("user/basicinformations/") + data[10]));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->put(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::PutProjectSettings(QVector<QString> &data)
{
    QJsonObject json;

    json["token"] = data[0];
    json["projectId"] = data[1];
    if (data[2] != "")
        json["name"] = data[2];
    if (data[3] != "")
        json["description"] = data[3];
    if (data[4] != "")
        json["logo"] = data[4];
    if (data[5] != "")
        json["phone"] = data[5];
    if (data[6] != "")
        json["company"] = data[6];
    if (data[7] != "")
        json["email"] = data[7];
    if (data[8] != "")
        json["facebook"] = data[8];
    if (data[9] != "")
        json["twitter"] = data[9];
    if (data[10] != "")
        json["password"] = data[10];

    QJsonDocument doc(json);
    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
    QNetworkRequest requestSend(QUrl(URL_API + QString("projects/updateinformations")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->put(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::Logout(QVector<QString> &data)
{
    QString url = URL_API + QString("accountadministration/logout");
    for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
    {
        url += QString("/") + *it;
    }

    QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

    return request;
}

QNetworkReply *DataConnectorOnline::GetAction(QString urlIn, QVector<QString> &data)
{
    QString url = URL_API + urlIn;
    for (QVector<QString>::const_iterator it = data.constBegin(); it != data.constEnd(); ++it)
    {
        url += QString("/") + *it;
    }
    QNetworkReply *request = _Manager->get(QNetworkRequest(QUrl(url)));

    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));

    return request;
}

QNetworkReply *DataConnectorOnline::AddRole(QVector<QString> &data)
{
    QJsonObject json;

    json["_token"] = data[0];
    json["projectId"] = data[1];
    json["name"] = data[2];
    json["teamTimeline"] = data[3];
    json["customerTimeline"] = data[4];
    json["gantt"] = data[5];
    json["whiteboard"] = data[6];
    json["bugtracker"] = data[7];
    json["event"] = data[8];
    json["task"] = data[9];
    json["projectSettings"] = data[10];
    json["cloud"] = data[11];

    QJsonDocument doc(json);
    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
    QNetworkRequest requestSend(QUrl(URL_API + QString("roles/addprojectroles")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->post(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::DeleteProjectRole(QVector<QString> &data)
{
    QJsonObject json;

    json["_token"] = data[0];
    json["projectId"] = data[1];
    json["roleId"] = data[2];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("roles/delprojectroles")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;

}

QNetworkReply *DataConnectorOnline::AttachRole(QVector<QString> &data)
{
    QJsonObject json;

    json["_token"] = data[0];
    json["projectId"] = data[1];
    json["userId"] = data[2];
    json["roleId"] = data[3];

    QJsonDocument doc(json);
    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
    QNetworkRequest requestSend(QUrl(URL_API + QString("roles/assignpersontorole")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->post(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::DetachRole(QVector<QString> &data)
{
    QJsonObject json;


    json["_token"] = data[0];
    json["projectId"] = data[1];
    json["userId"] = data[2];
    json["roleId"] = data[3];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("roles/delpersonrole")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    qDebug() << *jsonba;
    QNetworkReply *request = _Manager->sendCustomRequest(requestSend,QByteArray("DELETE"), new QBuffer(jsonba));
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::ProjectInvite(QVector<QString> &data)
{
    QJsonObject json;

    json["token"] = data[0];
    json["projectId"] = data[1];
    json["userEmail"] = data[2];

    QJsonDocument doc(json);
    QByteArray jsonba = doc.toJson(QJsonDocument::Compact);
    QNetworkRequest requestSend(QUrl(URL_API + QString("projects/addusertoproject")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba.size());
    QNetworkReply *request = _Manager->post(requestSend, jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::DeleteProjectUser(QVector<QString> &data)
{
    QJsonObject json;

    json["token"] = data[0];
    json["projectId"] = data[1];
    json["userId"] = data[2];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("projects/removeusertoproject")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::DeleteCustomerAccess(QVector<QString> &data)
{
    QJsonObject json;

    json["token"] = data[0];
    json["projectId"] = data[1];
    json["customerAccessId"] = data[2];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("projects/delcustomeraccess")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::DeleteProject(QVector<QString> &data)
{
    QJsonObject json;

    json["token"] = data[0];
    json["projectId"] = data[1];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("projects/delproject")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->sendCustomRequest(requestSend, QByteArray("DELETE"), new QBuffer(jsonba));
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::EditBug(QVector<QString> &data)
{
    QJsonObject json;
    QJsonArray  tags;

    //data[0] = id dans l'URL
    json["token"] = data[1];
    json["title"] = data[2];
    json["description"] = data[3];
    json["userId"] = data[4];
    json["stateId"] = data[5];
    json["stateName"] = data[6];
    for (int i = 7; i < data.length() - 1; ++i)
    {
        QJsonObject newTag;

        newTag["id"] = data[i++];
        newTag["name"] = data[i];
        tags.append(newTag);
    }
    json["parentId"] = data[data.length() - 1];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("bugtracker/editticket/") + data[0]));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->post(requestSend, *jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::OpenBug(QVector<QString> &data)
{
    QJsonObject json;
    QString idProject = data[0];

    json["token"] = data[1];
    json["title"] = data[2];
    json["description"] = data[3];
    json["stateId"] = data[4];
    json["stateName"] = data[5];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("bugtracker/postticket/") + idProject));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->post(requestSend, *jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::CommentBug(QVector<QString> &data)
{
    QJsonObject json;
    QString idProject = data[0];

    json["token"] = data[1];
    json["title"] = data[2];
    json["description"] = data[3];
    json["parentId"] = data[4];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("bugtracker/postticket/") + idProject));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->post(requestSend, *jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::CustomerGenerateAccess(QVector<QString> &data)
{
    QJsonObject json;

    json["token"] = data[0];
    json["projectId"] = data[1];
    json["name"] = data[2];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("projects/generatecustomeraccess")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->post(requestSend, *jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}

QNetworkReply *DataConnectorOnline::EditMessageTimeline(QVector<QString> &data)
{

}

QNetworkReply *DataConnectorOnline::PostMessageTimeline(QVector<QString> &data)
{

}

QNetworkReply *DataConnectorOnline::AssignTagToBug(QVector<QString> &data)
{
    QJsonObject json;

    json["token"] = data[0];
    json["bugId"] = data[1];
    json["tagId"] = data[2];

    QJsonDocument doc(json);
    QByteArray *jsonba = new QByteArray(doc.toJson(QJsonDocument::Compact));
    QNetworkRequest requestSend(QUrl(URL_API + QString("bugtracker/assigntag")));
    requestSend.setHeader(QNetworkRequest::ContentTypeHeader, "application/json");
    requestSend.setHeader(QNetworkRequest::ContentLengthHeader, jsonba->size());
    QNetworkReply *request = _Manager->put(requestSend, *jsonba);
    QObject::connect(request, SIGNAL(finished()), this, SLOT(OnResponseAPI()));
    return request;
}
