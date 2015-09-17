#ifndef IDATACONNECTOR
#define IDATACONNECTOR

#include <QList>
#include <QString>

namespace API
{

    enum DataPart
    {
        WHITEBOARD,
        PROJECT,
        GANTT,
        TASK,
        TIMELINE,
        USER_DATA,
        CALENDAR
    };

    enum GetRequest
    {
        CALENDAR,
        CALENDAR_DAY,
        LIST_GANTT,
        LIST_PROJECT,
        LIST_TASK,
        LIST_TIMELINE,
        PROJECT,
        TASK,
        TIMELINE,
        USER_DATA,
        WHITEBOARD
    };

    class IDataConnector
    {
    public:
        virtual ~IDataConnector {};

        virtual bool Update(DataPart part, int id, QList<QString> data) = 0;
        virtual bool Get(DataPart part, int id, QList<QString> data) = 0;
        virtual bool Delete(DataPart part, int id, QList<QString> data) = 0;

    };

}

#endif // IDATACONNECTOR

