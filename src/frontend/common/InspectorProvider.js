import React, { createContext, useContext, useState } from 'react';

// Create a context
const InspectorContext = createContext();

export const useInspector = () => useContext(InspectorContext);

export const InspectorProvider = ({ children }) => {
  const [inspectorState, setInspectorState] = useState({});

  return (
    <InspectorContext.Provider value={{ inspectorState, setInspectorState }}>
      {children}
    </InspectorContext.Provider>
  );
};
