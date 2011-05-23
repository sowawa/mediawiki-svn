#include <antlr3defs.h>
#include <mwparsercontext.h>
#include <mwgarbage.h>

static void beginGarbage(MWPARSERCONTEXT * context, pANTLR3_VECTOR attr);
static void endGarbage(MWPARSERCONTEXT * context);

static void
beginGarbage(MWPARSERCONTEXT * context, pANTLR3_VECTOR attr)
{
    MW_DELAYED_CALL(        context, beginGarbage, endGarbage, attr, true);
    MWLISTENER *l = &context->listener;
    l->beginGarbage(l);
}

static void
endGarbage(MWPARSERCONTEXT * context)
{
    MW_SKIP_IF_EMPTY(     context, beginGarbage, endGarbage);
    MWLISTENER *l = &context->listener;
    l->endGarbage(l);
}

void mwGarbageInit(MWPARSERCONTEXT *context)
{
    context->beginGarbage             = beginGarbage;
    context->endGarbage               = endGarbage;
}
