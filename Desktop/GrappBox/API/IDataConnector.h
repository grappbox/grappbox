#ifndef IDATACONNECTOR
#define IDATACONNECTOR

#include <QVector>
#include <QString>
#include <QObject>
#include <QImage>

namespace API
{

    enum DataPart
    {
        DP_WHITEBOARD,
        DP_PROJECT,
        DP_GANTT,
        DP_TASK,
        DP_TIMELINE,
        DP_USER_DATA,
        DP_CALENDAR,
        DP_BUGTRACKER
    };

    enum GetRequest
    {
        GR_CALENDAR,
        GR_CALENDAR_DAY,
        GR_LIST_GANTT,
        GR_LIST_PROJECT,
        GR_PROJECT,
        GR_CREATOR_PROJECT,
        GR_LIST_MEMBER_PROJECT,
        GR_LIST_MEETING,
        GR_LIST_TASK,
        GR_LIST_TIMELINE,
        GR_TASK,
        GR_TIMELINE,
        GR_COMMENT_TIMELINE,
        GR_ARCHIVE_MESSAGE_TIMELINE,
        GR_USER_DATA,
        GR_WHITEBOARD,
        GR_LOGOUT,
        GR_USER_SETTINGS,
        GR_PROJECTS_USER,
        GR_PROJECT_ROLE,
        GR_PROJECT_USERS,
        GR_PROJECT_CANCEL_DELETE,
        GR_PROJECT_USER_ROLE,
        GR_CUSTOMER_ACCESSES,
        GR_CUSTOMER_ACCESS_BY_ID,
        GR_USERPROJECT_BUG,
        GR_XLAST_BUG_OFFSET,
        GR_XLAST_BUG_OFFSET_BY_STATE,
        GR_XLAST_BUG_OFFSET_CLOSED,
        GR_PROJECTBUG_ALL,
        GR_BUGCOMMENT,
        GR_GETBUGS_STATUS,
        GR_PROJECTBUGTAG_ALL,
        GR_PROJECT_USERS_ALL,
        GR_BUG,
		GR_EVENT,
    };

    enum PostRequest
    {
        PR_LOGIN,
        PR_ROLE_ADD,
        PR_ROLE_ASSIGN,
        PR_CUSTOMER_GENERATE_ACCESS,
        PR_EDIT_BUG,
        PR_CREATE_BUG,
        PR_COMMENT_BUG,
        PR_ASSIGNUSER_BUG,
        PR_DELETEUSER_BUG,
        PR_CREATETAG,
        PR_MESSAGE_TIMELINE,
        PR_EDIT_MESSAGE_TIMELINE,
        PR_EDIT_COMMENTBUG,
		PR_POST_EVENT
    };

    enum DeleteRequest
    {
        DR_PROJECT_ROLE,
        DR_ROLE_DETACH,
        DR_PROJECT_USER,
        DR_PROJECT,
        DR_CUSTOMER_ACCESS,
        DR_CLOSE_TICKET_OR_COMMENT,
        DR_REMOVE_BUGTAG,
		DR_REMOVE_EVENT
    };

    enum PutRequest
    {
        PUTR_USERSETTINGS,
        PUTR_PROJECTSETTINGS,
        PUTR_INVITE_USER,
        PUTR_ASSIGNTAG,
		PUTR_EDIT_EVENT,
		PUTR_SET_PARTICIPANT
    };

    class IDataConnector
    {

    public:

        virtual ~IDataConnector() {}

        virtual int Post(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure) = 0;
        virtual int Get(DataPart part, int request, QVector<QString> &data, QObject *requestReturn, const char* slotSuccess, const char* slotFailure) = 0;
        virtual int Delete(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure) = 0;
        virtual int Put(DataPart part, int request, QVector<QString> &data, QObject *requestResponseObject, const char* slotSuccess, const char* slotFailure) = 0;

    };

    struct UserInformation
    {
        int id;
        QString firstName;
        QString lastName;
        QImage avatar;
        QString email;
        QString phone;
        QString country;
        QString linkedIn;
        QString viadeo;
        QString twitter;
    };
}

#endif // IDATACONNECTOR

