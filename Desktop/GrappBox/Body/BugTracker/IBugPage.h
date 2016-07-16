#ifndef IBUGPAGE
#define IBUGPAGE

#include "BodyBugTracker.h"
#include <QJsonObject>

class IBugPage
{
public:
    virtual void Show(BodyBugTracker *pageManager, QJsonObject *data) = 0;
    virtual void Hide() = 0;
};

#endif // IBUGPAGE

